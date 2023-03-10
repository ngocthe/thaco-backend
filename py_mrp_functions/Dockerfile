FROM python:3.8-slim-buster

RUN apt-get update \
  && apt-get install -y supervisor lsb-release build-essential curl libpq-dev software-properties-common --no-install-recommends \
  && apt-get install -y redis-server

COPY requirements.txt requirements.txt

RUN pip install -U setuptools \
  && pip install -r requirements.txt \
  && rm -rf /var/lib/apt/lists/* \
  && rm -rf /usr/share/doc && rm -rf /usr/share/man \
  && apt-get purge -y --auto-remove build-essential \
  && apt-get clean

ENV FLASK_DEBUG="False" \
    PYTHONUNBUFFERED="true" \
    LC_ALL="C.UTF-8" \
    LANG="C.UTF-8"

COPY . .

EXPOSE 3508
EXPOSE 3555
EXPOSE 6379

# needs to be set else Celery gives an error (because docker runs commands inside container as root)
ENV C_FORCE_ROOT=1

CMD ["/usr/bin/supervisord"]
