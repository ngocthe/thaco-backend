import os
import dotenv
dotenv.load_dotenv(".env.local")

from mrp_functions.tasks import mrp_calculation
import time


# Press the green button in the gutter to run the script.
if __name__ == '__main__':
    time_start = time.time()
    mrp_calculation(
        production_plan_id=int(os.environ['PRODUCTION_PLAN_ID']),
        msc_code=os.environ['MSC_CODE'],
        mrp_run_date=os.environ['MRP_RUN_DATE'],
        plant_code=os.environ['PLANT_CODE'],
        user_code=int(os.environ['USER_CODE']),
        parent_start_timestamp=time_start,
        simulation=True
    )
