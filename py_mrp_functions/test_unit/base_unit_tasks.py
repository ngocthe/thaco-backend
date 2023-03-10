from mrp_functions.base_tasks import TaskBase
import pandas as pd


class UnitTaskBase(TaskBase):

    # _late_engine = None
    # _db_url = None
    # _lock_table = False

    def db_connection_health_check(self):
        return "Unit Okay"

    @property
    def db_url(self):
        raise NotImplementedError("Should not be here!")

    @property
    def db_connection(self):
        raise NotImplementedError("Should not be here!")

    def open_raw_query(self, query_text) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def db_lock_table(self):
        raise NotImplementedError("Should not be here!")

    def db_close(self):
        print('Database Closed!')

    def clear_logical_inventory_forecast(self, mrp_run_date, plant_code):
        raise NotImplementedError("Should not be here!")

    def clear_mrp_results_forecast(self, mrp_run_date, production_plan_id, plant_code, simulation=True):
        raise NotImplementedError("Should not be here!")

    def clear_shortage_parts_forecast(self, mrp_run_date, production_plan_id, plant_code):
        raise NotImplementedError("Should not be here!")

    def query_list_distinct_msc_from_production_plan(self, production_plan_id, plant_code) -> list:
        raise NotImplementedError("Should not be here!")

    def query_parts_of_msc_from_boms(self, msc_code, plant_code) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def query_msc_volume_from_production_plan(self, production_plan_id, msc_code, mrp_run_date,
                                              plant_code) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def store_mrp_results(self, mrp_results_list_dict: list, simulation=True):
        raise NotImplementedError("Should not be here!")

    def query_inventory_log(self, mrp_run_date, plant_code) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def store_logical_inventory_results(self, log_inventory_list_dict: list):
        raise NotImplementedError("Should not be here!")

    def store_shortage_parts_results(self, shortage_parts_list: list):
        raise NotImplementedError("Should not be here!")

    def query_shortage_parts(self, mrp_run_date, production_plan_id, part_group) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def query_mrp_order_calendars(self, part_group, contract_code) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def query_date_from_week_definition(self, week_start_no, month_start_no, year_start_no,
                                        week_end_no, month_end_no, year_end_no) -> pd.DataFrame:
        raise NotImplementedError("Should not be here!")

    def query_list_date_from_Nplus1_to_Nplus6_definition(self, start_date, end_date) -> list:
        raise NotImplementedError("Should not be here!")

    def store_order_list(self, order_list: list):
        raise NotImplementedError("Should not be here!")
