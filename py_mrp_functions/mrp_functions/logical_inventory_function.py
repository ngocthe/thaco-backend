from datetime import datetime
from typing import Tuple, Union, Any, Optional

import pandas as pd
import numpy as np
from pandas import DataFrame

from mrp_functions import logging
from mrp_functions.base_tasks import TaskBase


def upsert_order_eta_to_logical_inventory_forecast(tb: TaskBase, mrp_run_date, production_plan_id,
                                                   list_mrp_date, plant_code, user_code, simulation) -> bool:
    try:
        # Get list of order which has ETA in that time
        list_order_eta_in_chunk = tb.query_current_logical_inventory_w_order_list_eta(mrp_run_date, plant_code)
        # Process in chunk to avoid limit memory usage
        for list_order_eta in list_order_eta_in_chunk:
            list_order_eta = list_order_eta.pivot_table(
                index="production_date",
                columns=["part_code", "part_color_code"],
                values="quantity",
                aggfunc='sum')

            # Convert order eta to Logical Inventory Forecast
            sim_logical_parts_inv = pd.DataFrame(np.zeros((len(list_mrp_date), len(list_order_eta.columns))),
                                                 index=list_mrp_date)
            sim_logical_parts_inv.index.names = ['production_date']
            sim_logical_parts_inv.columns = pd.MultiIndex.from_tuples(list_order_eta.columns,
                                                                      names=['part_code', 'part_color_code'])
            sim_logical_parts_inv = (sim_logical_parts_inv + list_order_eta).fillna(0.0).cumsum()

            sim_logical_parts_inv = sim_logical_parts_inv.stack().reset_index()
            sim_logical_parts_inv = sim_logical_parts_inv.set_index(
                ['production_date', 'part_color_code']).stack().reset_index()
            sim_logical_parts_inv.rename(columns={sim_logical_parts_inv.columns[-1]: 'quantity'}, inplace=True)

            sim_logical_parts_inv['plant_code'] = plant_code
            sim_logical_parts_inv['created_by'] = user_code
            sim_logical_parts_inv['updated_by'] = user_code
            # Upsert to Logical Inventory
            tb.store_logical_inventory_results(sim_logical_parts_inv.to_dict(orient="records"),
                                               upsert=False, is_simulation=simulation)

        return True
    except Exception as ex:
        logging.error(f"[{production_plan_id}_{mrp_run_date}_{plant_code}] "
                      f"Update Order ETA for Logical Inventory Forecast Failed!\n{ex}")
    return False


def calculate_logical_inventory(log_inventory_frame: pd.DataFrame,
                                mrp_results_frame: pd.DataFrame,
                                list_mrp_date: list,
                                is_simulation=False) -> Tuple[Optional[DataFrame], Optional[DataFrame]]:
    try:
        log_inventory_frame = log_inventory_frame.pivot_table(
            index="production_date",
            columns=["part_code", "part_color_code"],
            values="quantity",
            aggfunc='sum')
        mrp_results_frame = mrp_results_frame.pivot_table(
            index="production_date",
            columns=["part_code", "part_color_code"],
            values="quantity",
            aggfunc='sum')

        merge_logical_parts_columns = mrp_results_frame.columns.tolist()
        for _col in log_inventory_frame.columns:
            if _col not in merge_logical_parts_columns:
                merge_logical_parts_columns.append(_col)

        merge_logical_parts_inv = pd.DataFrame(np.zeros((len(list_mrp_date), len(merge_logical_parts_columns))),
                                               index=list_mrp_date)
        merge_logical_parts_inv.index.names = ['production_date']
        merge_logical_parts_inv.columns = pd.MultiIndex.from_tuples(merge_logical_parts_columns,
                                                                    names=['part_code', 'part_color_code'])

        merge_mrp_result_parts = (merge_logical_parts_inv + mrp_results_frame.fillna(0.0)).fillna(0.0)

        if len(log_inventory_frame) == 0:
            merge_logical_parts_inv = merge_logical_parts_inv.fillna(0.0)
        else:
            merge_logical_parts_inv = (merge_logical_parts_inv + log_inventory_frame).fillna(0.0)

        final_logical_inventory = merge_logical_parts_inv - merge_mrp_result_parts.cumsum()

        final_shortage_parts = None
        if is_simulation:
            final_shortage_parts = final_logical_inventory.copy(deep=True)
            final_shortage_parts = final_shortage_parts.applymap(lambda x: np.minimum(0.0, x))
            final_shortage_parts = final_shortage_parts - final_shortage_parts.shift(1).copy(deep=True).fillna(0.0)

        return final_logical_inventory, final_shortage_parts
    except Exception as ex:
        logging.error(f"Calculate Logical Inventory Failed!\n{ex}")
    # Return empty DataFrames or None
    return pd.DataFrame(), None
