from waitress import serve
import os
from mrp_functions.main import application
from dotenv import load_dotenv

# Load environment variables from .env if presented
load_dotenv()


if __name__ == '__main__':
    flask_host = os.environ['FLASK_HOST']
    flask_port = os.environ['FLASK_PORT']
    serve(application,
          host=flask_host,
          port=flask_port,
          threads=2)
    pass
