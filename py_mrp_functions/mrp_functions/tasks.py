from datetime import datetime

import pandas as pd

from celery_workers import celery
from mrp_functions import logging
import time
import numpy as np
import re

from mrp_functions.logical_inventory_function import calculate_logical_inventory, \
    upsert_order_eta_to_logical_inventory_forecast
from mrp_functions.mrp_function import calculate_mrp_result


class MrpRunException(Exception):

    message = ''
    code = 10200

    def __init__(self, message, code):
        self.message = message
        self.code = code


@celery.task()
def task_health():
    return task_health.db_connection_health_check()


@celery.task()
def mrp_system_run(production_plan_id, mrp_run_date, user_code, parent_start_timestamp,
                   simulation=True, detach=False) -> str:
    mrp_or_status = 2
    if simulation:
        mrp_or_status = 1
    try:
        mrp_system_run.reset_calculation_progress(production_plan_id)

        # To lock All Tables involves before work to avoid data calculation wrong
        mrp_system_run.db_lock_table()

        # Get list of all distinct MSC Code, Vehicle Color Code, Plant Code to run MRP
        list_msc_plant_code = mrp_system_run.query_list_distinct_msc_from_production_plan(production_plan_id)
        logging.info(f'[{production_plan_id}_{mrp_run_date}_{user_code}] '
                     f'Number of MSCs from Production Plan: {len(list_msc_plant_code)}')

        # Get end date of production plan
        end_date_of_prod_plan = mrp_system_run.query_end_date_from_production_plan(production_plan_id)
        if end_date_of_prod_plan is None:
            raise MrpRunException("Production Plan End Date is None!", 10200)
        # Get list of all date from N to N+6 to end of production Plan
        list_mrp_date = mrp_system_run.query_list_date_from_Nplus1_to_Nplus6_definition(mrp_run_date,
                                                                                        end_date_of_prod_plan)
        logging.info(f"[{production_plan_id}_{mrp_run_date}_{user_code}] "
                     f"System Run From: {list_mrp_date[0]} To: {list_mrp_date[-1]}")

        list_distinct_plant_code = []
        for _, _, _plant_code in list_msc_plant_code:
            if _plant_code not in list_distinct_plant_code:
                # To delete Forecast Logical Inventory from current date and plant code
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                             f"Clear logical inventory Forecast - [Simulation]")
                mrp_system_run.clear_logical_inventory_forecast(mrp_run_date, _plant_code, True)
                if not simulation:
                    logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                                 f"Clear logical inventory Forecast - [Forecast]")
                    mrp_system_run.clear_logical_inventory_forecast(mrp_run_date, _plant_code, False)
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                             f"Clear logical inventory Forecast finishes after: "
                             f"{time.time() - parent_start_timestamp:.2f} sec")

                # To delete Forecast Results from current date, import id and plant code
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                             f"Clear MRP Result - [Simulation: {simulation}]")
                mrp_system_run.clear_mrp_results_forecast(mrp_run_date, production_plan_id, _plant_code, simulation)
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                             f"Clear MRP Result finishes after: {time.time() - parent_start_timestamp:.2f} sec")

                # To delete future Shortage Parts Quantity from current date and mapped to this production plan
                if simulation:
                    logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                                 f"Clear Shortage parts forecast")
                    mrp_system_run.clear_shortage_parts_forecast(mrp_run_date, production_plan_id, _plant_code)
                    logging.info(f"[{production_plan_id}_{mrp_run_date}_{_plant_code}_{user_code}] "
                                 f"Clear Shortage parts forecast finishes after: "
                                 f"{time.time() - parent_start_timestamp:.2f} sec")

                list_distinct_plant_code.append(_plant_code)

                upsert_order_eta_to_logical_inventory_forecast(mrp_system_run, mrp_run_date, production_plan_id,
                                                               list_mrp_date, _plant_code, user_code, simulation=True)

        progress_finish = np.ceil(100 / len(list_msc_plant_code))
        # run MRP Calculation for each msc
        for _msc_code, _vehicle_color_code, _plant_code in list_msc_plant_code:
            mrp_calculation(mrp_system_run, production_plan_id, _msc_code, _vehicle_color_code,
                            mrp_run_date, list_mrp_date, mrp_or_status, _plant_code,
                            user_code, parent_start_timestamp, progress_finish, simulation)
        # run inventory Calculation for each msc
        for _plant_code in list_distinct_plant_code:
            logical_inventory_calculation(mrp_system_run, production_plan_id, mrp_run_date, list_mrp_date,
                                          mrp_or_status, _plant_code, user_code, parent_start_timestamp,
                                          progress_finish=100, simulation=simulation)
    except MrpRunException as mrp_ex:
        logging.error(f'[{production_plan_id}_{mrp_run_date}_{user_code}] '
                      f"MRP System Run Failed. Simulation = {simulation}\n{mrp_ex}")
        mrp_system_run.update_calculation_status(production_plan_id, mrp_or_status,
                                                 result=mrp_ex.code, increase_progress=-1)
    except Exception as ex:
        logging.error(f'[{production_plan_id}_{mrp_run_date}_{user_code}] System Error\n{ex}')
        mrp_system_run.update_calculation_status(production_plan_id, mrp_or_status,
                                                 result=10200, increase_progress=-1)
    # Close Db connection and release Locks
    mrp_system_run.db_close()
    return f'MRP System Run finished after: {(time.time() - parent_start_timestamp) / 60:.2f} min'


def logical_inventory_calculation(tb, production_plan_id, mrp_run_date, list_mrp_date, mrp_or_status, plant_code,
                                  user_code, parent_start_timestamp, progress_finish=0, simulation=True) -> str:
    try:
        logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                     f"[Sim: {simulation}] Inventory Calculation Starts")
        # To query current Logical Inventory Quantity
        log_inventory_frame = tb.query_inventory_log(mrp_run_date, plant_code, is_simulation=True)
        logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                     f"Current Inventory Size: {len(log_inventory_frame)}")

        mrp_results_frame = tb.query_mrp_results(mrp_run_date, plant_code, production_plan_id, simulation)
        logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                     f"MRP Results Size: {len(mrp_results_frame)}")
        # No.2 ##### To calculate Logical Inventory for this MRP ######
        log_inventory, log_parts_shortage = calculate_logical_inventory(
            log_inventory_frame, mrp_results_frame, list_mrp_date, simulation)

        logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                     f"Calculated Logical Inventory Size: {len(log_inventory)}")

        # to save Logical Inventory to database
        if len(log_inventory) > 0:
            # Ignore current run date from Result of Logical Inventory
            log_inventory = log_inventory.loc[
                log_inventory.index > pd.to_datetime(datetime.strptime(mrp_run_date, "%Y-%m-%d"))].copy(deep=True)
            log_inventory = log_inventory.stack().reset_index()
            log_inventory = log_inventory.set_index(['production_date', 'part_color_code']).stack().reset_index()
            log_inventory.rename(columns={log_inventory.columns[-1]: 'quantity'}, inplace=True)

            log_inventory[['plant_code']] = plant_code
            log_inventory[['created_by']] = user_code
            log_inventory[['updated_by']] = user_code

            logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                         f"Logical Inventory Size: {len(log_inventory)}")
            logging.debug(f"[{plant_code}] Logical Inventory:\n{log_inventory.to_dict(orient='records')}")
            tb.store_logical_inventory_results(log_inventory.to_dict(orient="records"),
                                               upsert=False, is_simulation=simulation)
            logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                         f"Calculate Logical Inventory finished after: {time.time() - parent_start_timestamp:.2f} sec")
            del log_inventory
        else:
            logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                         f"Logical Inventory Forecast is Empty!")
            raise MrpRunException(f"Logical Inventory Forecast is Empty!", 10207)

        if simulation:
            # to save Shortage Parts to database
            if log_parts_shortage is not None and len(log_parts_shortage) > 0:
                log_parts_shortage = log_parts_shortage.loc[
                    log_parts_shortage.index > pd.to_datetime(datetime.strptime(mrp_run_date, "%Y-%m-%d"))]
                log_parts_shortage = log_parts_shortage.stack().reset_index()
                log_parts_shortage = log_parts_shortage.set_index(
                    ['production_date', 'part_color_code']).stack().reset_index()

                log_parts_shortage.rename(columns={log_parts_shortage.columns[-1]: 'quantity'}, inplace=True)
                # To ignore all positive or zero quantity
                log_parts_shortage = log_parts_shortage.loc[log_parts_shortage.quantity < 0].copy(deep=True)

                log_parts_shortage[['import_id']] = production_plan_id
                log_parts_shortage[['plant_code']] = plant_code
                log_parts_shortage[['created_by']] = user_code
                log_parts_shortage[['updated_by']] = user_code

                logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                             f"Shortage Parts Size: {len(log_parts_shortage)}")
                logging.debug(f"[{plant_code}] Shortage Parts:\n{log_parts_shortage.to_dict(orient='records')}")
                # to save Shortage Parts
                tb.store_shortage_parts_results(log_parts_shortage.to_dict(orient="records"))
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                             f"Store Shortage Parts finished after: {time.time() - parent_start_timestamp:.2f} sec")
            else:
                logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                             f"There's no Shortage Parts!")
                raise MrpRunException(f"There's no Shortage Parts!", 10204)

        tb.update_calculation_status(production_plan_id, mrp_or_status, result=None,
                                     increase_progress=progress_finish)
    except MrpRunException as ex:
        raise ex
    except Exception as ex:
        logging.error(f"[{plant_code}] MRP Calculation Run Failed. Simulation = {simulation}\n{ex}")
        tb.update_calculation_status(production_plan_id, mrp_or_status,
                                     result=10200, increase_progress=progress_finish)

    # ##### Done ######
    logging.info(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                 f"Task Finish after: {(time.time() - parent_start_timestamp) / 60:.2f} min")

    return f"[{plant_code}] Finish after: {(time.time() - parent_start_timestamp) / 60:.2f} min " \
           f"({(time.time() - parent_start_timestamp):.2f} sec)"


def mrp_calculation(tb, production_plan_id, msc_code, vehicle_color_code,
                    mrp_run_date, list_mrp_date, mrp_or_status, plant_code,
                    user_code, parent_start_timestamp, progress_finish=0, simulation=True) -> str:
    try:
        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                     f"[Sim: {simulation}] MRP Calculation Starts")
        # For each MSC, to query all part required from BOM Master Table
        each_msc_parts_need_frame = tb.query_parts_of_msc_from_boms(msc_code, plant_code)
        if each_msc_parts_need_frame is None or len(each_msc_parts_need_frame) == 0:
            raise MrpRunException(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                                  f"MRP has no valid Part", 10202)

        # For each MSC, to query all production volume from Production Table
        production_volume = tb.query_msc_volume_from_production_plan(production_plan_id, msc_code, vehicle_color_code,
                                                                     mrp_run_date, plant_code)
        if production_volume is None or len(production_volume) == 0:
            raise MrpRunException(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                                  f"MRP has no Production Volume", 10201)
        mrp_parts_quantity = calculate_mrp_result(msc_code, production_plan_id,
                                                  mrp_run_date, plant_code,
                                                  each_msc_parts_need_frame,
                                                  production_volume, tb)
        if mrp_parts_quantity is None or len(mrp_parts_quantity) == 0:
            raise MrpRunException(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                                  f"MSC Calculation results empty", 10202)

        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                     f"[Sim: {simulation}] Calculate MRP Results Size: {len(mrp_parts_quantity)}")

        # to save MRP Results
        mrp_parts_quantity[['created_by']] = user_code
        mrp_parts_quantity[['updated_by']] = user_code

        logging.debug(f"[{msc_code}] MRP Results:\n{mrp_parts_quantity.to_dict(orient='records')}")

        tb.store_mrp_results(mrp_parts_quantity.to_dict(orient="records"), simulation)

        logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                     f"[Sim: {simulation}] Store MRP Result finished after: {time.time() - parent_start_timestamp:.2f} sec")
    except MrpRunException as ex:
        raise ex
    except Exception as ex:
        logging.error(f"[{msc_code}] MRP Calculation run failed. Simulation = {simulation}\n{ex}")
        raise MrpRunException(f"MRP System Error", 10200)

    # ##### Done ######
    logging.info(f"[{msc_code}_{production_plan_id}_{mrp_run_date}_{plant_code}_{user_code}] "
                 f"Task Finish after: {(time.time() - parent_start_timestamp) / 60:.2f} min")

    return f"[{msc_code}] Finish after: {(time.time() - parent_start_timestamp) / 60:.2f} min " \
           f"({(time.time() - parent_start_timestamp):.2f} sec)"


@celery.task(ignore_result=True, autoretry_for=(Exception,), retry_backoff=True, max_retries=1)
def order_list_generation(production_plan_id, mrp_run_date, part_group, contract_code,
                          user_code, parent_start_timestamp):
    mrp_or_status = 3
    try:
        logging.info(f"[{production_plan_id}_{mrp_run_date}_{user_code}] "
                     f"Order Generation Starts")
        order_list_generation.reset_calculation_progress(production_plan_id)
        # Get Ordering Calendar Data
        mrp_or_calendar_frame = order_list_generation.query_mrp_order_calendars(part_group, contract_code)

        if mrp_or_calendar_frame is None or len(mrp_or_calendar_frame) == 0:
            raise MrpRunException(f"MRP Ordering Calendar Not Found for Part Group: {part_group} "
                                  f"and Contract Code: {contract_code}", 10205)
        logging.debug(f"MRP Ordering Calendar:\n{mrp_or_calendar_frame.to_dict(orient='records')}")

        target_from, target_to, buffer_from, buffer_to = mrp_or_calendar_frame.loc[
            0, ["target_plan_from", "target_plan_to", "buffer_span_from", "buffer_span_to"]].values

        logging.info(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                     f"Target From: {target_from}. Target To: {target_to}. Buffer: {buffer_from}~{buffer_to}")

        _, target_from_week, target_from_month_no, target_from_year = re.split("\D", target_from)
        _, target_to_week, target_to_month_no, target_to_year = re.split("\D", target_to)
        if buffer_from is not None and len(buffer_from) > 0 and buffer_to is not None and len(buffer_to) > 0:
            _, buffer_from_week, buffer_from_month_no, buffer_from_year = re.split("\D", buffer_from)
            _, buffer_to_week, buffer_to_month_no, buffer_to_year = re.split("\D", buffer_to)

            target_date = order_list_generation.query_date_from_week_definition(
                target_from_week, target_from_month_no, target_from_year,
                buffer_to_week, buffer_to_month_no, buffer_to_year)
        else:
            target_date = order_list_generation.query_date_from_week_definition(
                target_from_week, target_from_month_no, target_from_year,
                target_to_week, target_to_month_no, target_to_year)

        if target_date is None or len(target_date) < 2:
            raise MrpRunException(f"Target Plan is invalid for Part Group: {part_group} "
                                  f"and Contract Code: {contract_code}", 10203)

        logging.info(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                     f"Target Date: {target_date[0]} To: {target_date[-1]}")
        shortage_parts = order_list_generation.query_shortage_parts(mrp_run_date, production_plan_id, part_group,
                                                                    target_date[0], target_date[-1])
        if len(shortage_parts) == 0:
            logging.info(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                         f"There s no shortage within production plan !")
            raise MrpRunException(f"There s no shortage within production plan", 10204)
        else:
            logging.debug(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}] "
                          f"Order Shortage:\n{shortage_parts.to_dict(orient='records')}")
            # Filter duplicated Shortage Parts from query!
            shortage_parts = shortage_parts.loc[~shortage_parts.duplicated(keep='first')]
            order_list = shortage_parts.groupby(["part_code", "part_color_code", "part_group", "supplier_code",
                                                 "minimum_order_quantity", "standard_box_quantity",
                                                 "part_quantity_in_box", "import_id", "plant_code"
                                                 ]).agg({"quantity": sum})

            order_list.reset_index(inplace=True)

            order_list[['eta']] = mrp_or_calendar_frame.iloc[0].eta
            order_list[['contract_code']] = mrp_or_calendar_frame.iloc[0].contract_code
            order_list[['user_code']] = user_code
            order_list[['mrp_quantity']] = np.abs(order_list[['quantity']])

            order_list['quantity'] = order_list.apply(
                lambda row: np.multiply(row['part_quantity_in_box'],
                                        np.ceil(np.divide(
                                            np.max((np.abs(row['quantity']), row['minimum_order_quantity'])),
                                            row['part_quantity_in_box']))), axis=1)

            logging.info(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                         f"Order List size: {len(order_list)}")
            logging.debug(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}] "
                          f"Order List:\n{order_list.to_dict(orient='records')}")
            order_list_generation.store_order_list(order_list.to_dict(orient="records"))

        order_list_generation.update_calculation_status(production_plan_id, mrp_or_status,
                                                        result=None, increase_progress=100.0)
        logging.info(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                     f"Order Generate Task Finish after: {(time.time() - parent_start_timestamp) / 60:.2f} min")
    except MrpRunException as ex:
        logging.error(f'[{production_plan_id}_{mrp_run_date}_{user_code}] '
                      f"Order List Generation Failed\n{ex}")
        mrp_system_run.update_calculation_status(production_plan_id, mrp_or_status,
                                                 result=ex.code, increase_progress=-1)
    except Exception as ex:
        logging.error(f"[{contract_code}_{part_group}_{production_plan_id}_{mrp_run_date}_{user_code}] "
                      f"Order List Generation Failed\n{ex}")
        order_list_generation.update_calculation_status(production_plan_id, mrp_or_status,
                                                        result=10206, increase_progress=-1)
    order_list_generation.db_close()
