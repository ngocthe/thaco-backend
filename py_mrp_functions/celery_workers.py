from celery import Celery
import os
from dotenv import load_dotenv

# Load environment variables from .env if presented
load_dotenv()


CELERY_BROKER_URL = os.environ['CELERY_BROKER_URL']
CELERY_RESULT_BACKEND = os.environ['CELERY_RESULT_BACKEND']
CODE_NAME = "ORH_IT_SYS"


celery = Celery(CODE_NAME,
                broker=CELERY_BROKER_URL, backend=CELERY_RESULT_BACKEND,
                task_cls=os.getenv('CELERY_UNIT_TEST_TASK_CLASS', 'mrp_functions.base_tasks:TaskBase'),
                include=['mrp_functions.tasks'])

# Optional configuration, see the application user guide.
celery.conf.update(
    result_expires=3600,
)


@celery.task(bind=True)
def debug_task(self):
    # Test if celery is correctly setup
    print(f'Request: {self.request!r}')
    pass
