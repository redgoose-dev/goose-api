ARG PYTHON_VERSION="3.13"

# Builder stage
FROM python:${PYTHON_VERSION}-alpine AS builder
ARG PYTHON_VERSION
WORKDIR /app

# Install UV
RUN apk add --no-cache curl && curl -LsSf https://astral.sh/uv/install.sh | sh
ENV PATH="/root/.local/bin:$PATH"

# Install dependencies
COPY pyproject.toml .
RUN uv sync
RUN uv pip compile pyproject.toml -o requirements.txt
RUN uv pip install --system -r requirements.txt


# Runtime stage
FROM python:${PYTHON_VERSION}-alpine
ARG PYTHON_VERSION
WORKDIR /app

# Copy dependencies from builder stage
COPY --from=builder /usr/local/lib/python${PYTHON_VERSION}/site-packages /usr/local/lib/python${PYTHON_VERSION}/site-packages
COPY --from=builder /usr/local/bin /usr/local/bin

# Copy application files
COPY src ./src
COPY resource ./resource
COPY install.py .
COPY main.py .

# run entrypoint
RUN chmod +x resource/docker-entrypoint.sh
ENTRYPOINT [ "resource/docker-entrypoint.sh" ]
