# UnrealIRCd with JSON-RPC, only for the panel's integration tests.
FROM debian:bookworm
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential pkg-config git ca-certificates openssl \
    libssl-dev libpcre2-dev libargon2-dev libsodium-dev libc-ares-dev libjansson-dev \
    && rm -r /var/lib/apt/lists/*
RUN useradd -m ircd
USER ircd
# Pinned UnrealIRCd commit (6.x, 2026-11-06), so test results do not change under us
ARG UNREALIRCD_COMMIT=89acd7d5e5d1d8a307cf3f2fde27bc86a3670037
RUN git clone https://github.com/unrealircd/unrealircd /home/ircd/src \
 && git -C /home/ircd/src checkout -q ${UNREALIRCD_COMMIT}
WORKDIR /home/ircd/src
COPY --chown=ircd:ircd config.settings /home/ircd/src/config.settings
RUN ./Config -quick && make -j8 && make install
RUN openssl req -x509 -newkey rsa:2048 -nodes -days 3650 -subj "/CN=irc.test" \
      -keyout /home/ircd/unrealircd/conf/tls/server.key.pem \
      -out /home/ircd/unrealircd/conf/tls/server.cert.pem
WORKDIR /home/ircd/unrealircd
EXPOSE 6667 8600
CMD ["/home/ircd/unrealircd/bin/unrealircd", "-F"]
