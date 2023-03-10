import datetime

from test_unit.base_unit_tasks import UnitTaskBase
import pandas as pd


class UnitTask1(UnitTaskBase):

    def query_list_distinct_msc_from_production_plan(self, production_plan_id, plant_code) -> list:
        return ['MSC1', 'MSC2']

    def query_parts_of_msc_from_boms(self, msc_code, plant_code) -> pd.DataFrame:
        msc1 = [
            {
                "msc_code": "MSC1",
                "part_code": "Pt01",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "Pt02",
                "part_color_code": "2",
                "part_quantity": 2,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "Pt03",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "Pt04",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2022-11-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "05Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "06Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "07Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "08Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "09Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "10Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "11Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "12Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "13Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "14Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "15Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "16Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "17Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "18Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC1",
                "part_code": "19Pt",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            }
        ]
        msc2 = [
            {
                "msc_code": "MSC2",
                "part_code": "Pt01",
                "part_color_code": "30",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC2",
                "part_code": "Pt02",
                "part_color_code": "2",
                "part_quantity": 2,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC2",
                "part_code": "Pt03",
                "part_color_code": "2",
                "part_quantity": 3,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC2",
                "part_code": "Pt04",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            },
            {
                "msc_code": "MSC2",
                "part_code": "Pt05",
                "part_color_code": "2",
                "part_quantity": 1,
                "ecn_in_date": "2021-12-01",
                "ecn_out_date": "2024-12-15",
                "plant_code": "TMAC"
            }
        ]
        df = pd.DataFrame()
        if msc_code == 'MSC1':
            df = pd.DataFrame(data=msc1)
        elif msc_code == 'MSC2':
            df = pd.DataFrame(data=msc2)
        df['ecn_in_date'] = pd.to_datetime(df['ecn_in_date']).dt.date
        df['ecn_out_date'] = pd.to_datetime(df['ecn_out_date']).dt.date
        return df

    def query_msc_volume_from_production_plan(self, production_plan_id, msc_code, mrp_run_date,
                                              plant_code) -> pd.DataFrame:
        msc1 = [
            {
                "plan_date": datetime.date(2022, 10, 31),
                "msc_code": "MSC1",
                "vehicle_color_code": "GT7",
                "volume": 70
            },
            {
                "plan_date": datetime.date(2022, 11, 1),
                "msc_code": "MSC1",
                "vehicle_color_code": "GT7",
                "volume": 70
            },
            {
                "plan_date": datetime.date(2022, 11, 3),
                "msc_code": "MSC1",
                "vehicle_color_code": "GT7",
                "volume": 50
            }
        ]
        msc2 = [
            {
                "plan_date": datetime.date(2022, 8, 31),
                "msc_code": "MSC2",
                "vehicle_color_code": "GT7",
                "volume": 20
            }
        ]
        if msc_code == 'MSC1':
            return pd.DataFrame(data=msc1)
        elif msc_code == 'MSC2':
            return pd.DataFrame(data=msc2)
        return pd.DataFrame()

    def store_mrp_results(self, mrp_results_list_dict: list, simulation=True):
        # TODO
        print('Not implemented yet. TODO: Assert Equality')

    def query_inventory_log(self, mrp_run_date, plant_code) -> pd.DataFrame:
        prod_date = datetime.date(2022, 8, 30)
        d = [
            {
                'production_date': prod_date,
                'part_code': 'Pt01',
                'part_color_code': '2',
                'quantity': 100
            },
            {
                'production_date': prod_date,
                'part_code': 'Pt02',
                'part_color_code': '2',
                'quantity': 100
            },
            {
                'production_date': prod_date,
                'part_code': 'Pt01',
                'part_color_code': '30',
                'quantity': 100
            },
            {
                'production_date': prod_date,
                'part_code': 'Pt03',
                'part_color_code': '2',
                'quantity': 50
            },
            # {
            #     'production_date': datetime.date(2022, 8, 31),
            #     'part_code': 'Pt04',
            #     'part_color_code': '2',
            #     'quantity': 50
            # },
            # {
            #     'production_date': datetime.date(2022, 9, 3),
            #     'part_code': 'Pt01',
            #     'part_color_code': '30',
            #     'quantity': 50
            # },
            # {
            #     'production_date': datetime.date(2022, 9, 3),
            #     'part_code': 'Pt03',
            #     'part_color_code': '2',
            #     'quantity': 100
            # }
        ]
        return pd.DataFrame(data=d)

    def store_logical_inventory_results(self, log_inventory_list_dict: list):
        # TODO
        print('Not implemented yet. TODO: Assert Equality')

    def store_shortage_parts_results(self, shortage_parts_list: list):
        # TODO
        print('Not implemented yet. TODO: Assert Equality')

    def query_shortage_parts(self, mrp_run_date, production_plan_id, part_group) -> pd.DataFrame:
        d = {
            'production_date': ['2022-08-30', '2022-08-31', '2022-10-31', '2022-11-01', '2022-11-03', ],
            'part_code': ['Pt01', 'Pt02', 'Pt01', 'Pt03', ],
            'part_color_code': ['2', '2', '30', '2', ],
            'quantity': [100, 100, 100, 50, ],
            'part_group': ['BP', 'BP', 'BP', 'BP', ],
            'minimum_order_quantity': [10, 10, 10, 10, ],
            'standard_box_quantity': [5, 5, 5, 5, ],
            'part_quantity_in_box': [50, 50, 50, 50, ],
            'unit': ['PIECES', 'PIECES', 'PIECES', 'PIECES', ],
            'supplier_code': ['HCH', 'HCH', 'HCH', 'HCH', ],
            'import_id': [1, 1, 1, 1, ],
            'plant_code': ['TMAC', 'TMAC', 'TMAC', 'TMAC', ]
        }
        df = pd.DataFrame(data=d)
        df['production_date'] = pd.to_datetime(df['production_date']).dt.date
        return df

    def query_mrp_order_calendars(self, part_group, contract_code) -> pd.DataFrame:
        d = [{
            'mrp_ord_calendar_id': 1,
            'contract_code': 'VJORH01',
            'etd': '2022-10-01',
            'eta': '2022-10-30',
            'target_plan_from': 'W1-11/2022',
            'target_plan_to': 'W2-11/2022',
            'buffer_span_from': 'W3-11/2022',
            'buffer_span_to': 'W3-11/2022',
            'status': 1,
        }]
        df = pd.DataFrame(data=d)
        df['etd'] = pd.to_datetime(df['etd']).dt.date
        df['eta'] = pd.to_datetime(df['eta']).dt.date
        return df

    def query_date_from_week_definition(self, week_start_no, month_start_no, year_start_no,
                                        week_end_no, month_end_no, year_end_no) -> pd.DataFrame:
        d = [
            {
                "id": 1035,
                "date": "2022-10-31",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1036,
                "date": "2022-11-01",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1037,
                "date": "2022-11-02",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1038,
                "date": "2022-11-03",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1039,
                "date": "2022-11-04",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1040,
                "date": "2022-11-05",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1041,
                "date": "2022-11-06",
                "day_off": 1,
                "year": 2022,
                "month_no": 11,
                "week_no": 1,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1042,
                "date": "2022-11-07",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1043,
                "date": "2022-11-08",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1044,
                "date": "2022-11-09",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1045,
                "date": "2022-11-10",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1046,
                "date": "2022-11-11",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1047,
                "date": "2022-11-12",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1048,
                "date": "2022-11-13",
                "day_off": 1,
                "year": 2022,
                "month_no": 11,
                "week_no": 2,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1049,
                "date": "2022-11-14",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1050,
                "date": "2022-11-15",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1051,
                "date": "2022-11-16",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1052,
                "date": "2022-11-17",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1053,
                "date": "2022-11-18",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1054,
                "date": "2022-11-19",
                "day_off": 0,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            },
            {
                "id": 1055,
                "date": "2022-11-20",
                "day_off": 1,
                "year": 2022,
                "month_no": 11,
                "week_no": 3,
                "created_by": 1,
                "updated_by": 1,
            }
        ]

        df = pd.DataFrame(data=d)
        df['date'] = pd.to_datetime(df['date']).dt.date
        return df

    def store_order_list(self, order_list: list):
        # TODO
        print('Not implemented yet. TODO: Assert Equality')
