ARG PYTHON_VERSION=3.13.3
FROM python:${PYTHON_VERSION}-alpine AS builder

WORKDIR /app

# Install UV
RUN apk add --no-cache curl && curl -LsSf https://astral.sh/uv/install.sh | sh
ENV PATH="/root/.cargo/bin:$PATH"

# Install dependencies
COPY pyproject.toml .
RUN uv pip compile pyproject.toml -o requirements.txt
RUN uv pip install --system -r requirements.txt

# Runtime stage
FROM python:${PYTHON_VERSION}-alpine

WORKDIR /app

# 필요한 런타임 패키지만 복사
COPY --from=builder /usr/local/lib/python${PYTHON_VERSION}/site-packages /usr/local/lib/python${PYTHON_VERSION}/site-packages
COPY --from=builder /usr/local/bin /usr/local/bin

# 애플리케이션 코드 복사
COPY src /app/src
COPY scripts /app/scripts
COPY resource /app/resource
COPY install.py /app
COPY main.py /app

# run entrypoint
RUN chmod +x scripts/docker-entrypoint.sh
ENTRYPOINT [ "scripts/docker-entrypoint.sh" ]
