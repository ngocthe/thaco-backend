import os
import time
from datetime import datetime
import pandas as pd
import unittest

os.environ.setdefault('CELERY_UNIT_TEST_TASK_CLASS', 'unittest.tasks_unit1:UnitTask1')

from mrp_functions.logical_inventory_function import calculate_logical_inventory


class TestLogicalInventoryCorrect(unittest.TestCase):
    def test_cal_logical_inventory_correct_1(self):
        # Prepare Test Data
        _log_inv_frame = pd.DataFrame([
            {'production_date': datetime.date(datetime(2022, 8, 30)),
             'part_code': 'Pt01',
             'part_color_code': '2',
             'quantity': 100},
            {'production_date': datetime.date(datetime(2022, 8, 30)),
             'part_code': 'Pt01',
             'part_color_code': '30',
             'quantity': 100},
            {'production_date': datetime.date(datetime(2022, 8, 30)),
             'part_code': 'Pt02',
             'part_color_code': '2',
             'quantity': 100},
            {'production_date': datetime.date(datetime(2022, 8, 30)),
             'part_code': 'Pt03',
             'part_color_code': '2',
             'quantity': 50}
        ])

        _mrp_part_codes = [('Pt01', '30'), ('Pt02', '2'), ('Pt03', '2'), ('Pt04', '2'), ('Pt05', '2')]
        _mrp_result_parts = pd.DataFrame([[20, 40, 60, 20, 20]],
                                         index=[datetime.date(datetime(2022, 8, 31))])
        _mrp_result_parts.index.names = ['production_date']
        _mrp_result_parts.columns = pd.MultiIndex.from_tuples(_mrp_part_codes, names=['part_code', 'part_color_code'])

        # Actual Result
        calculation_log_parts = calculate_logical_inventory(_log_inv_frame, _mrp_result_parts, "2022-08-31")

        # Expected Result
        _log_part_codes = [('Pt01', '2'), ('Pt01', '30'), ('Pt02', '2'), ('Pt03', '2'), ('Pt04', '2'), ('Pt05', '2')]
        _expected_log_result = pd.DataFrame([[100, 100, 100, 50, 0, 0], [100, 80, 60, -10, -20, -20]],
                                            index=[datetime.date(datetime(2022, 8, 30)),
                                                   datetime.date(datetime(2022, 8, 31))])
        _expected_log_result.index.names = ['production_date']
        _expected_log_result.columns = pd.MultiIndex.from_tuples(_log_part_codes, names=['part_code',
                                                                                         'part_color_code'])

        pd.testing.assert_frame_equal(_expected_log_result, calculation_log_parts, check_dtype=False)


if __name__ == '__main__':
    unittest.main(verbosity=2)
