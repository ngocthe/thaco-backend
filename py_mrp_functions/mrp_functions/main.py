import os
from mrp_functions import logging
import time
from flask import Flask, request
from celery_workers import celery
from mrp_functions.tasks import mrp_system_run, order_list_generation, task_health


application = Flask(__name__, instance_relative_config=True)
application.config.from_mapping(
    SECRET_KEY=os.environ['FLASK_SECRET']
)


@application.route('/health')
def hello():
    return task_health()


@application.route("/mrp/v1/result/<task_id>")
def get_results(task_id: str):
    res = celery.AsyncResult(task_id)
    if res.state == "SUCCESS":
        return {"status": res.state, "message": res.get()}
    return {"status": res.state}


@application.route(f'/mrp/v1/simulation_run', methods=['POST'])
def mrp_system_simulation_task() -> str:
    start_time = time.time()
    _params = request.form
    if _params is not None:
        production_plan_id = _params.get("production_plan_id", None)
        user_code = _params.get("user_code", None)
        # plant_code = _params.get("plant_code", None)
        mrp_run_date = _params.get("mrp_run_date", None)
        simulation = True
        detach = True
        # executes in 1 seconds from now
        mrp_system_run.apply_async(
            countdown=int(os.getenv("DELAY_TRIGGER_TASK_SECOND", 1)),
            args=[production_plan_id, mrp_run_date, user_code, start_time, simulation, detach])
        # mrp_system_run(production_plan_id, mrp_run_date, user_code, start_time, simulation, detach)
        logging.info([production_plan_id, mrp_run_date, user_code, start_time, simulation, detach])
        return f"Okay. Request received: {', '.join((production_plan_id, user_code, mrp_run_date))}"
    return "Received message request but no param specified"


@application.route(f'/mrp/v1/system_run', methods=['POST'])
def mrp_system_run_task() -> str:
    parent_start_timestamp = time.time()
    _params = request.form
    if _params is not None:
        production_plan_id = _params.get("production_plan_id", None)
        user_code = _params.get("user_code", None)
        # plant_code = _params.get("plant_code", None)
        mrp_run_date = _params.get("mrp_run_date", None)
        simulation = False
        detach = True
        # executes in 1 seconds from now !
        mrp_system_run.apply_async(
            countdown=int(os.getenv("DELAY_TRIGGER_TASK_SECOND", 1)),
            args=[production_plan_id, mrp_run_date, user_code, parent_start_timestamp, simulation, detach])
        # mrp_system_run(production_plan_id, mrp_run_date, user_code, parent_start_timestamp, simulation, detach)
        return f"Okay. Request received: {', '.join((production_plan_id, user_code, mrp_run_date))}"
    return "Received message request but no param specified"


@application.route(f'/mrp/v1/order_run', methods=['POST'])
def mrp_order_task() -> str:
    start_time = time.time()
    _params = request.form
    if _params is not None:
        production_plan_id = _params.get("production_plan_id", None)
        user_code = _params.get("user_code", None)
        # plant_code = _params.get("plant_code", None)
        mrp_run_date = _params.get("mrp_run_date", None)
        part_group = _params.get("part_group", None)
        contract_code = _params.get("contract_code", None)
        # executes in 1 seconds from now !
        order_list_generation.apply_async(
            countdown=int(os.getenv("DELAY_TRIGGER_TASK_SECOND", 1)),
            args=[production_plan_id, mrp_run_date, part_group, contract_code, user_code, start_time])
        # order_list_generation(production_plan_id, mrp_run_date, part_group, contract_code, user_code, start_time)
        return f"Okay. Request received: " \
               f"{', '.join((production_plan_id, mrp_run_date, part_group, contract_code, user_code))} "
    return "Received message request but no param specified"

