from typing import Iterator

from sqlalchemy import create_engine
from mrp_functions import logging
from celery import Task
from sqlalchemy import text
import pandas as pd
import os
from datetime import datetime


class TaskBase(Task):
    _late_engine = None
    _db_url = None
    _lock_table = False

    @property
    def db_url(self):
        if self._db_url is None:
            self._db_url = 'mysql+pymysql://{user}:{pw}@{host}:{port}/{db}'.format(
                user=os.environ['DB_USERNAME'],
                pw=os.environ['DB_PASSWORD'],
                host=os.environ['DB_HOST'],
                port=os.environ['DB_PORT'],
                db=os.environ['DB_DATABASE'])
        return self._db_url

    @property
    def db_connection(self):
        # Create database url from config
        if self._late_engine is None:
            logging.info("Init connection engine")
            self._late_engine = create_engine(self.db_url).connect()
        return self._late_engine

    def db_connection_health_check(self):
        try:
            _cur_results = self.db_connection.execute(text("""select 1 as is_alive;"""))
            for row in _cur_results:
                logging.debug(f'connection health= {row}')
        except Exception as ex:
            logging.error(f"Unable to connect to SQL\n{ex}")
            return "Unable to connect to SQL"
        self.db_close()
        return "Okay"

    def open_raw_query(self, query_text) -> pd.DataFrame:
        resultFrame = None
        with create_engine(self.db_url).connect() as connection:
            resultFrame = pd.read_sql(text(query_text), con=connection)
        return resultFrame

    def db_lock_table(self):
        self.db_connection.execute(text("LOCK TABLES "
                                        "boms READ, "
                                        "ecns READ, "
                                        "ecns msc_ecn_in READ, "
                                        "ecns msc_ecn_out READ, "
                                        "ecns part_ecn_in READ, "
                                        "ecns part_ecn_out READ, "
                                        "production_plans READ, "
                                        "mrp_order_calendars READ, "
                                        "mrp_week_definitions READ, "
                                        "mrp_week_definitions mwd READ, "
                                        "mscs READ, "
                                        "order_calendars READ, "
                                        "part_colors pc READ, "
                                        "parts p READ, "
                                        "vehicle_colors vc READ, "
                                        "procurements READ, "
                                        "suppliers READ, "
                                        "mrp_production_plan_imports WRITE, "
                                        "shortage_parts WRITE, "
                                        "logical_inventories WRITE, "
                                        "logical_inventory_simulations WRITE, "
                                        "mrp_results WRITE, "
                                        "mrp_simulation_results WRITE, "
                                        "order_lists WRITE"
                                        ";"))
        logging.debug("LOCKED TABLES !")
        self._lock_table = True

    def db_close(self):
        if self._late_engine is not None:
            if self._lock_table is True:
                self._late_engine.execute(text("UNLOCK TABLES;"))
                logging.info("UNLOCKED TABLES !")
                self._lock_table = False
            self._late_engine.close()
            self._late_engine = None
            logging.info("DB Connection Closed")

    def clear_logical_inventory_forecast(self, mrp_run_date, plant_code, is_simulation=True):
        table_name = 'logical_inventories'
        if is_simulation:
            table_name = 'logical_inventory_simulations'
        self.db_connection.execute(text(f"""
                        DELETE FROM {table_name}
                        WHERE production_date > '{mrp_run_date}' and plant_code = '{plant_code}';"""))

    def clear_mrp_results_forecast(self, mrp_run_date, production_plan_id, plant_code, simulation=True):
        if simulation:
            delete_from_mrp_table = f"""
                    DELETE FROM mrp_simulation_results
                    WHERE plan_date > '{mrp_run_date}'
                    and import_id = {production_plan_id}
                    and plant_code = '{plant_code}';"""
            soft_delete_old_mrp_table = f"""
                    UPDATE mrp_simulation_results
                    SET deleted_at = '{mrp_run_date}'
                    WHERE plan_date <= '{mrp_run_date}'
                    and import_id = {production_plan_id}
                    and plant_code = '{plant_code}'
                    and deleted_at is null;"""
        else:
            delete_from_mrp_table = f"""
                    DELETE FROM mrp_results
                    WHERE production_date > '{mrp_run_date}'
                    and import_id = {production_plan_id}
                    and plant_code = '{plant_code}';"""
            soft_delete_old_mrp_table = f"""
                    UPDATE mrp_results
                    SET deleted_at = '{mrp_run_date}'
                    WHERE production_date <= '{mrp_run_date}'
                    and import_id = {production_plan_id}
                    and plant_code = '{plant_code}'
                    and deleted_at is null;"""

        self.db_connection.execute(text(delete_from_mrp_table))
        self.db_connection.execute(text(soft_delete_old_mrp_table))

    def clear_shortage_parts_forecast(self, mrp_run_date, production_plan_id, plant_code):
        # Delete old Forecast for the same plant code and production plan
        self.db_connection.execute(text(f"""
                DELETE FROM shortage_parts
                WHERE plan_date > '{mrp_run_date}'
                and import_id = {production_plan_id}
                and plant_code = '{plant_code}';"""))
        # Soft Delete old shortage for the same plant code and production plan
        self.db_connection.execute(text(f"""
                UPDATE shortage_parts
                SET deleted_at = '{mrp_run_date}'
                WHERE plan_date <= '{mrp_run_date}'
                and import_id = {production_plan_id}
                and plant_code = '{plant_code}'
                and deleted_at is null;"""))

    def query_list_distinct_msc_from_production_plan(self, production_plan_id) -> list:
        list_msc = []
        _cur_results = self.db_connection.execute(text(f"""
        SELECT DISTINCT msc_code, vehicle_color_code, plant_code
        FROM production_plans
        WHERE import_id = {production_plan_id} and deleted_at is null;"""))
        for row in _cur_results:
            list_msc.append((
                dict(row).get("msc_code"), dict(row).get("vehicle_color_code"), dict(row).get("plant_code")
            ))
        return list_msc

    def query_parts_of_msc_from_boms(self, msc_code, plant_code) -> pd.DataFrame:
        parts_needs_query = f"""SELECT
            boms.msc_code,
            boms.part_code,
            boms.part_color_code,
            boms.quantity as part_quantity,
            msc_ecn_in.code as bom_msc_ecn_in_code,
            msc_ecn_out.code as bom_msc_ecn_out_code,
            msc_ecn_in.planned_line_off_date as bom_ecn_in_date,
            msc_ecn_out.planned_line_off_date as bom_ecn_out_date,
            part_ecn_in.code as part_ecn_in_code,
            part_ecn_out.code as part_ecn_out_code,
            part_ecn_in.planned_line_off_date as part_ecn_in_date,
            part_ecn_out.planned_line_off_date as part_ecn_out_date,
            boms.plant_code
        FROM boms
        LEFT OUTER JOIN ecns msc_ecn_in on boms.ecn_in = msc_ecn_in.code and boms.plant_code = msc_ecn_in.plant_code
        LEFT OUTER JOIN ecns msc_ecn_out on boms.ecn_out = msc_ecn_out.code and boms.plant_code = msc_ecn_out.plant_code
        INNER JOIN parts p on boms.part_code = p.code and boms.plant_code = p.plant_code
        LEFT OUTER JOIN ecns part_ecn_in on p.ecn_in = part_ecn_in.code and p.plant_code = part_ecn_in.plant_code
        LEFT OUTER JOIN ecns part_ecn_out on p.ecn_out = part_ecn_out.code and p.plant_code = part_ecn_out.plant_code
        WHERE
            boms.msc_code = '{msc_code}'
            and boms.plant_code = '{plant_code}'
            and boms.deleted_at is null
            and msc_ecn_in.deleted_at is null
            and msc_ecn_out.deleted_at is null
            and p.deleted_at is null
            and part_ecn_in.deleted_at is null
            and part_ecn_out.deleted_at is null
        ;"""
        return pd.read_sql(text(parts_needs_query), con=self.db_connection,
                           parse_dates=['bom_ecn_in_date',
                                        'bom_ecn_out_date',
                                        'part_ecn_in_date',
                                        'part_ecn_out_date'])

    def query_msc_volume_from_production_plan(self, production_plan_id, msc_code, vehicle_color_code,
                                              mrp_run_date, plant_code) -> pd.DataFrame:
        production_volume_query = f"""SELECT
            plan_date,
            msc_code,
            vehicle_color_code,
            volume,
            mwd.day_off
        FROM production_plans
        INNER JOIN mrp_week_definitions mwd on production_plans.plan_date = mwd.date
        WHERE import_id = {production_plan_id}
            and plan_date >= '{mrp_run_date}'
            and msc_code = '{msc_code}'
            and vehicle_color_code = '{vehicle_color_code}'
            and production_plans.deleted_at is null
        ORDER BY plan_date ASC;
        """
        return pd.read_sql(text(production_volume_query),
                           con=self.db_connection,
                           parse_dates=['plan_date'])

    def query_part_color_xx(self, part_code, vehicle_color_code, plant_code):
        query_part_color_text = f"""SELECT pc.code as part_color_code
        FROM part_colors pc
        INNER JOIN vehicle_colors vc on pc.vehicle_color_code = vc.code
                                            and pc.plant_code = vc.plant_code
        WHERE
            pc.part_code = '{part_code}'
            and pc.vehicle_color_code = '{vehicle_color_code}'
            and vc.type = 'EXT'
            and pc.plant_code = '{plant_code}'
            and pc.deleted_at is null
            and vc.deleted_at is null
        ;"""
        part_xx_code = []
        cursor = self.db_connection.execute(text(query_part_color_text))
        for row in cursor:
            part_xx_code.append(row['part_color_code'])
        if len(part_xx_code) != 1:
            return None
        return part_xx_code[0]

    def store_mrp_results(self, mrp_results_list_dict: list, simulation=True, chunk_size=1000):
        if mrp_results_list_dict is None or len(mrp_results_list_dict) == 0:
            return None
        if len(mrp_results_list_dict) > chunk_size:
            self.store_mrp_results(mrp_results_list_dict[:chunk_size], simulation)
            self.store_mrp_results(mrp_results_list_dict[chunk_size:len(mrp_results_list_dict)], simulation)
        else:
            upsert_mrp_result_sql = f"""
                    INSERT INTO mrp_results
                    (production_date, msc_code, vehicle_color_code, production_volume, part_code, part_color_code,
                    part_requirement_quantity, import_id, plant_code, created_by, updated_by, created_at, updated_at)
                    VALUES
                """
            if simulation:
                upsert_mrp_result_sql = f"""
                    INSERT INTO mrp_simulation_results
                    (plan_date, msc_code, vehicle_color_code, production_volume, part_code, part_color_code,
                    part_requirement_quantity, import_id, plant_code, created_by, updated_by, created_at, updated_at)
                    VALUES
                """
            mrp_values = []
            for row in range(len(mrp_results_list_dict)):
                mrp_values.append(
                    "(" + ", ".join(
                        (
                            f"'{str(mrp_results_list_dict[row].get('production_date'))}'",
                            f"'{str(mrp_results_list_dict[row].get('msc_code'))}'",
                            f"'{str(mrp_results_list_dict[row].get('vehicle_color_code'))}'",
                            str(mrp_results_list_dict[row].get('production_volume')),
                            f"'{str(mrp_results_list_dict[row].get('part_code'))}'",
                            f"'{str(mrp_results_list_dict[row].get('part_color_code'))}'",
                            str(mrp_results_list_dict[row].get('part_requirement_quantity')),
                            str(mrp_results_list_dict[row].get('import_id')),
                            f"'{str(mrp_results_list_dict[row].get('plant_code'))}'",
                            str(mrp_results_list_dict[row].get('created_by')),
                            str(mrp_results_list_dict[row].get('updated_by')),
                            f"'{datetime.now()}'",
                            f"'{datetime.now()}'"
                        )
                    ) + ")"
                )
            upsert_mrp_result_sql += ", ".join(mrp_values) + " ON DUPLICATE KEY UPDATE " \
                                                             " part_requirement_quantity = VALUES(part_requirement_quantity), " \
                                                             " updated_at = VALUES(updated_at)," \
                                                             " deleted_at = null;"
            logging.info(f"[Sim: {simulation}] Start storing MRP Results: {len(mrp_values)} ")
            self.db_connection.execute(text(upsert_mrp_result_sql))

    def query_inventory_log(self, mrp_run_date, plant_code, is_simulation=False) -> pd.DataFrame:
        table_name = 'logical_inventories'
        if is_simulation:
            table_name = 'logical_inventory_simulations'
        return pd.read_sql(text(f"""
            SELECT
                production_date,
                part_code,
                part_color_code,
                quantity
            FROM {table_name}
            WHERE plant_code = '{plant_code}' and
                production_date >= '{mrp_run_date}' - interval 1 day and
                deleted_at is null
            ORDER BY production_date DESC;"""), con=self.db_connection, parse_dates=['production_date'])

    def query_mrp_results(self, mrp_run_date, plant_code, import_id,
                          is_simulation=False) -> pd.DataFrame:
        query_text = f"""
            SELECT
                production_date,
                part_code,
                part_color_code,
                part_requirement_quantity as quantity
            FROM mrp_results
            WHERE plant_code = '{plant_code}' and
                production_date >= '{mrp_run_date}' and
                part_color_code != 'XX' and
                import_id = {import_id} and
                deleted_at is null
            ORDER BY production_date;"""
        if is_simulation:
            query_text = f"""
            SELECT
                plan_date as production_date,
                part_code,
                part_color_code,
                part_requirement_quantity as quantity
            FROM mrp_simulation_results
            WHERE plant_code = '{plant_code}' and
                plan_date >= '{mrp_run_date}' and
                part_color_code != 'XX' and
                import_id = {import_id} and
                deleted_at is null
            ORDER BY plan_date;"""
        return pd.read_sql(text(query_text), con=self.db_connection, parse_dates=['production_date'])

    def store_logical_inventory_results(self, log_inventory_list_dict: list,
                                        upsert=False, is_simulation=False,
                                        chunk_size=1000):
        if log_inventory_list_dict is None or len(log_inventory_list_dict) == 0:
            return
        if len(log_inventory_list_dict) > chunk_size:
            self.store_logical_inventory_results(log_inventory_list_dict[:chunk_size], upsert, is_simulation)
            self.store_logical_inventory_results(log_inventory_list_dict[chunk_size:len(log_inventory_list_dict)],
                                                 upsert, is_simulation)
        else:
            table_name = 'logical_inventories'
            if is_simulation:
                table_name = 'logical_inventory_simulations'
            upsert_log_inventory_sql = f"""
            INSERT INTO {table_name}
            (production_date, part_code, part_color_code, quantity, plant_code, created_by, updated_by, created_at, updated_at)
            VALUES
            """
            logical_inventory_values = []
            for row in range(len(log_inventory_list_dict)):
                logical_inventory_values.append("(" + ", ".join(
                    (
                        f"'{str(log_inventory_list_dict[row].get('production_date'))}'",
                        f"'{str(log_inventory_list_dict[row].get('part_code'))}'",
                        f"'{str(log_inventory_list_dict[row].get('part_color_code'))}'",
                        str(log_inventory_list_dict[row].get('quantity')),
                        f"'{str(log_inventory_list_dict[row].get('plant_code'))}'",
                        str(log_inventory_list_dict[row].get('created_by')),
                        str(log_inventory_list_dict[row].get('updated_by')),
                        f"'{datetime.now()}'",
                        f"'{datetime.now()}'"
                    )
                ) + ")")
            if upsert:
                upsert_log_inventory_sql += ", ".join(logical_inventory_values) + \
                                            " ON DUPLICATE KEY UPDATE" \
                                            " quantity = quantity + VALUES(quantity)," \
                                            " updated_at = VALUES(updated_at), " \
                                            " deleted_at = null;"
            else:
                upsert_log_inventory_sql += ", ".join(logical_inventory_values) + \
                                            " ON DUPLICATE KEY UPDATE" \
                                            " quantity = VALUES(quantity)," \
                                            " updated_at = VALUES(updated_at), " \
                                            " deleted_at = null;"

            logging.info(f"Storing Logical Inventory [Sim: {is_simulation}]: {len(logical_inventory_values)} ")
            self.db_connection.execute(text(upsert_log_inventory_sql))

    def store_shortage_parts_results(self, shortage_parts_list: list, chunk_size=1000):
        if shortage_parts_list is None or len(shortage_parts_list) == 0:
            return

        if len(shortage_parts_list) > chunk_size:
            self.store_shortage_parts_results(shortage_parts_list[:chunk_size])
            self.store_shortage_parts_results(shortage_parts_list[chunk_size:len(shortage_parts_list)])
        else:
            upsert_shortage_parts_sql = """
                INSERT INTO shortage_parts
                (plan_date, part_code, part_color_code, quantity, import_id,  plant_code,
                created_by, updated_by, created_at, updated_at)
                VALUES
                """
            shortage_parts_values = []
            for row in range(len(shortage_parts_list)):
                shortage_parts_values.append("(" + ", ".join(
                    (
                        f"'{str(shortage_parts_list[row].get('production_date'))}'",
                        f"'{str(shortage_parts_list[row].get('part_code'))}'",
                        f"'{str(shortage_parts_list[row].get('part_color_code'))}'",
                        str(shortage_parts_list[row].get('quantity')),
                        str(shortage_parts_list[row].get('import_id')),
                        f"'{str(shortage_parts_list[row].get('plant_code'))}'",
                        str(shortage_parts_list[row].get('created_by')),
                        str(shortage_parts_list[row].get('updated_by')),
                        f"'{datetime.now()}'",
                        f"'{datetime.now()}'"
                    )
                ) + ")")
            upsert_shortage_parts_sql += ", ".join(shortage_parts_values) + \
                                         " ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), " \
                                         " updated_at = VALUES(updated_at)," \
                                         " deleted_at = null;"
            logging.info(f"Storing Shortage: {len(shortage_parts_values)} ")
            self.db_connection.execute(text(upsert_shortage_parts_sql))

    def query_shortage_parts(self, mrp_run_date, production_plan_id, part_group, target_span_from, target_span_to) -> pd.DataFrame:
        raw_query = f"""
                SELECT
                    sp.plan_date as production_date,
                    sp.part_code,
                    sp.part_color_code,
                    sp.quantity,
                    p.`group` as part_group,
                    proc.minimum_order_quantity,
                    proc.standard_box_quantity,
                    proc.part_quantity as part_quantity_in_box,
                    proc.unit,
                    proc.supplier_code,
                    sp.import_id,
                    sp.plant_code
                FROM shortage_parts sp
                INNER JOIN parts p on sp.part_code = p.code and sp.plant_code = p.plant_code
                INNER JOIN part_groups pg on p.group = pg.code
                INNER JOIN procurements proc on p.code = proc.part_code
                                                and sp.part_color_code = proc.part_color_code
                                                and sp.plant_code = proc.plant_code
                INNER JOIN suppliers sup on proc.supplier_code = sup.code
                WHERE
                    sp.plan_date >= '{mrp_run_date}'
                    and sp.plan_date >= '{target_span_from}'
                    and sp.plan_date <= '{target_span_to}'
                    and sp.import_id = {production_plan_id}
                    and p.group = '{part_group}'
                    and sp.deleted_at is null
                    and p.deleted_at is null
                    and pg.deleted_at is null
                    and proc.deleted_at is null
                    and sup.deleted_at is null
                ORDER BY plan_date;"""
        return pd.read_sql(text(raw_query), con=self.db_connection, parse_dates=['production_date'])

    def query_mrp_order_calendars(self, part_group, contract_code) -> list:
        return pd.read_sql(text(f"""
            SELECT
                id as mrp_ord_calendar_id,
                contract_code,
                etd,
                eta,
                target_plan_from,
                target_plan_to,
                buffer_span_from,
                buffer_span_to,
                status
            FROM mrp_order_calendars
            WHERE part_group = '{part_group}' and status = 1 and contract_code = '{contract_code}'
                and deleted_at is null
        ;"""), con=self.db_connection)

    def query_date_from_week_definition(self, week_start_no, month_start_no, year_start_no,
                                        week_end_no, month_end_no, year_end_no) -> pd.DataFrame:
        get_target_start = f"""
        SELECT mrp_week_definitions.date
            FROM mrp_week_definitions
            WHERE
                week_no >= {week_start_no} and month_no >= {month_start_no} and year >= {year_start_no}
                and
                deleted_at is null
            ORDER BY date ASC
            LIMIT 1;
        """
        target_date = []
        cursor = self.db_connection.execute(text(get_target_start), parse_dates=['date'])
        for row in cursor:
            target_date.append(row['date'])
            break

        get_target_end = f"""
                SELECT mrp_week_definitions.date
                    FROM mrp_week_definitions
                    WHERE
                        week_no <= {week_end_no} and month_no <= {month_end_no} and year <= {year_end_no}
                        and
                        deleted_at is null
                    ORDER BY date DESC
                    LIMIT 1;
                """
        cursor = self.db_connection.execute(text(get_target_end), parse_dates=['date'])
        for row in cursor:
            target_date.append(row['date'])
            break

        return target_date

    def query_end_date_from_production_plan(self, production_plan_id) -> str:
        production_volume_query = f"""SELECT
            plan_date
        FROM production_plans
        WHERE import_id = {production_plan_id}
        ORDER BY plan_date DESC
        LIMIT 1;
        """
        plan_end_date = None
        cursor = self.db_connection.execute(text(production_volume_query), parse_dates=['plan_date'])
        for row in cursor:
            plan_end_date = row['plan_date']
        return plan_end_date

    def query_list_date_from_Nplus1_to_Nplus6_definition(self, start_date: str, end_date: str) -> list:
        query_text = f"""
            SELECT mwd0.date
                FROM mrp_week_definitions mwd0
                    WHERE
                        mwd0.date >= '{start_date}' AND
                        mwd0.date <= (SELECT mwd2.date as next3M_latest_date
                                      FROM (SELECT mwd.month_no,
                                                   IF(month_no <= 9, month_no + 3, mwd.month_no - 9) as next3M_Mth_no,
                                                   IF(month_no <= 9, mwd.year, mwd.year + 1) as next3M_year_no
                                            FROM mrp_week_definitions mwd
                                            WHERE mwd.date = '{start_date}') as temp
                                               INNER JOIN mrp_week_definitions mwd2 on mwd2.year = temp.next3M_year_no AND mwd2.month_no = temp.next3M_Mth_no
                                      ORDER BY mwd2.date DESc
                                      LIMIT 1)
                        AND
                        mwd0.date <= '{end_date}'

            UNION

            SELECT min(mwd0.date)
                FROM mrp_week_definitions mwd0
                    WHERE
                        mwd0.date > (SELECT mwd2.date as next6M_latest_date
                                      FROM (SELECT mwd.month_no,
                                                   IF(month_no <= 9, month_no + 3, mwd.month_no - 9) as next3M_Mth_no,
                                                   IF(month_no <= 9, mwd.year, mwd.year + 1) as next3M_year_no
                                            FROM mrp_week_definitions mwd
                                            WHERE mwd.date = '{start_date}') as temp
                                               INNER JOIN mrp_week_definitions mwd2 on mwd2.year = temp.next3M_year_no AND mwd2.month_no = temp.next3M_Mth_no
                                      ORDER BY mwd2.date DESc
                                      LIMIT 1)
                        AND
                        mwd0.date <= (SELECT mwd2.date as next6M_latest_date
                                      FROM (SELECT mwd.month_no,
                                                   IF(month_no <= 6, month_no + 6, mwd.month_no - 6)   as next6M_Mth_no,
                                                   IF(month_no <= 6, mwd.year, mwd.year + 1)           as next6M_year_no
                                            FROM mrp_week_definitions mwd
                                            WHERE mwd.date = '{start_date}') as temp
                                               INNER JOIN mrp_week_definitions mwd2 on mwd2.year = temp.next6M_year_no AND mwd2.month_no = temp.next6M_Mth_no
                                      ORDER BY mwd2.date DESc
                                      LIMIT 1)
                        AND
                        mwd0.date <= '{end_date}'
                    group by mwd0.week_no, mwd0.month_no

            UNION

            SELECT min(mwd0.date)
                FROM mrp_week_definitions mwd0
                    WHERE
                        mwd0.date > (SELECT mwd2.date as next6M_latest_date
                                      FROM (SELECT mwd.month_no,
                                                   IF(month_no <= 6, month_no + 6, mwd.month_no - 6)   as next6M_Mth_no,
                                                   IF(month_no <= 6, mwd.year, mwd.year + 1)           as next6M_year_no
                                            FROM mrp_week_definitions mwd
                                            WHERE mwd.date = '{start_date}') as temp
                                               INNER JOIN mrp_week_definitions mwd2 on mwd2.year = temp.next6M_year_no AND mwd2.month_no = temp.next6M_Mth_no
                                      ORDER BY mwd2.date DESC
                                      LIMIT 1)
                        AND
                        mwd0.date <= '{end_date}'
                    group by mwd0.month_no;"""
        list_date = []
        with create_engine(self.db_url).connect() as connection:
            cursor = connection.execute(text(query_text), parse_dates=['date'])
            for row in cursor:
                list_date.append(row['date'])

        return list_date

    def query_current_logical_inventory_w_order_list_eta(self, mrp_run_date, plant_code: str) -> Iterator[pd.DataFrame]:
        query_text = f"""
            SELECT
                production_date,
                part_code,
                part_color_code,
                quantity
            FROM logical_inventories
            WHERE plant_code = '{plant_code}' and
                production_date = '{mrp_run_date}' and
                deleted_at is null

            UNION

            SELECT
                eta as production_date,
                part_code,
                part_color_code,
                actual_quantity as quantity
            FROM order_lists
            where plant_code = '{plant_code}'
             and eta > '{mrp_run_date}'
             and status = 2
             and deleted_at is null
        """
        return pd.read_sql(text(query_text), con=self.db_connection, chunksize=3000, parse_dates=['production_date'])

    def store_order_list(self, order_list: list):
        for row in range(len(order_list)):
            upsert_order_parts_sql = """
                INSERT INTO order_lists
                (status, contract_code, eta, part_code, part_color_code, part_group, actual_quantity,
                         supplier_code, import_id, moq, mrp_quantity, plant_code,
                         created_by, updated_by, created_at, updated_at)
                VALUES
                """
            upsert_order_parts_sql += "(" + ", ".join(
                (
                    str(1),
                    f"'{str(order_list[row].get('contract_code'))}'",
                    f"'{str(order_list[row].get('eta'))}'",
                    f"'{str(order_list[row].get('part_code'))}'",
                    f"'{str(order_list[row].get('part_color_code'))}'",
                    f"'{str(order_list[row].get('part_group'))}'",
                    str(order_list[row].get('quantity')),
                    f"'{str(order_list[row].get('supplier_code'))}'",
                    str(order_list[row].get('import_id')),
                    str(order_list[row].get('minimum_order_quantity')),
                    str(order_list[row].get('mrp_quantity')),
                    f"'{str(order_list[row].get('plant_code'))}'",
                    str(order_list[row].get('user_code')),
                    str(order_list[row].get('user_code')),
                    f"'{datetime.now()}'",
                    f"'{datetime.now()}'"
                )
            ) + ")" + " ON DUPLICATE KEY UPDATE " \
                      "actual_quantity = VALUES(actual_quantity), " \
                      "mrp_quantity = VALUES(mrp_quantity), " \
                      "updated_at = VALUES(updated_at); "
            self.db_connection.execute(text(upsert_order_parts_sql))

    def reset_calculation_progress(self, production_plan_id):
        self.db_connection.execute(text(f"""
        UPDATE mrp_production_plan_imports
            SET mrp_or_progress=1, mrp_or_result="", updated_at = '{datetime.now()}'
        WHERE id = {production_plan_id};
        """))

    def update_calculation_status(self, production_plan_id, status, result, increase_progress=0.0):
        progress, current_result = self.get_calculation_status(production_plan_id)
        if increase_progress < 0:
            progress = -1
        else:
            progress = min(100, progress+increase_progress)
        update_values = [f"mrp_or_progress={progress}"]
        if status is not None:
            update_values.append(f"mrp_or_status={status}")
        if result is not None and str(result) not in current_result:
            update_values.append(f"mrp_or_result='{', '.join(filter(None, [current_result, str(result)]))}'")
        else:
            update_values.append(f"mrp_or_result=null")
        update_values.append(f"updated_at = '{datetime.now()}'")
        self.db_connection.execute(text(f"""
        UPDATE mrp_production_plan_imports
            SET {", ".join(update_values)}
        WHERE id = {production_plan_id};
        """))

    def get_calculation_status(self, production_plan_id):
        progress = 0.0
        current_result = ''
        _cur_results = self.db_connection.execute(text(f"""
        SELECT mrp_or_progress, mrp_or_result
        FROM mrp_production_plan_imports
        WHERE id = {production_plan_id} and deleted_at is null LIMIT 1;"""))
        for row in _cur_results:
            progress = dict(row).get("mrp_or_progress")
            current_result = dict(row).get("mrp_or_result")
            if current_result is None:
                current_result = ""
        return progress, current_result
