import logging
from logging.handlers import RotatingFileHandler
from os import path
import os

log_dir = path.join(path.dirname(__file__), '..', 'log')
if not path.exists(log_dir):
    os.mkdir(log_dir)

log_path = path.join(log_dir, 'flask.log')

log_formatter = logging.Formatter("[%(asctime)s] [%(name)-12.12s] [%(levelname)-5.5s]  %(message)s")
root_logger = logging.getLogger()
# Logging Level
logging_level = {
    'CRITICAL': 50,
    'ERROR': 40,
    'WARNING': 30,
    'INFO': 20,
    'DEBUG': 10,
    'NOTSET': 0,
    'NONE': 20
}
root_logger.setLevel(logging_level.get(os.getenv("LOG_LEVEL", "NONE").upper()))

file_handler = RotatingFileHandler(filename=log_path,
                                   maxBytes=1024 * 1024 * 5,
                                   backupCount=10,
                                   encoding='utf-8',
                                   delay=False)
file_handler.setFormatter(log_formatter)
root_logger.addHandler(file_handler)

console_handler = logging.StreamHandler()
console_handler.setFormatter(log_formatter)
root_logger.addHandler(console_handler)
