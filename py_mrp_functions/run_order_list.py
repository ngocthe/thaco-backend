import os
import dotenv
dotenv.load_dotenv(".env.local")

from mrp_functions.tasks import order_list_generation
import time


# Press the green button in the gutter to run the script.
if __name__ == '__main__':
    order_list_generation(
        production_plan_id=int(os.environ['PRODUCTION_PLAN_ID']),
        mrp_run_date=os.environ['MRP_RUN_DATE'],
        part_group=os.environ['PART_GROUP'],
        contract_code=os.environ['CONTRACT_CODE'],
        user_code=int(os.environ['USER_CODE']),
        parent_start_timestamp=time.time()
    )
