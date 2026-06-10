# ---------------------------------------------------------------------------
# Build the Nuxt app
# ---------------------------------------------------------------------------
FROM node:22-alpine AS build

WORKDIR /app

ENV NUXT_TELEMETRY_DISABLED=1

# Install deps. We use `npm install` (not `npm ci`) on purpose: Nuxt 4 pulls in
# native/optional packages (rolldown, oxc, @emnapi) whose lockfile entries npm
# rewrites on every install, so `npm ci`'s strict sync check can never be
# satisfied. `--ignore-scripts` skips the `postinstall: nuxt prepare` here,
# which would fail because the deps layer only has package.json (no source);
# `npm run build` runs prepare itself once the full source is present.
COPY package.json package-lock.json ./
RUN npm install --no-audit --no-fund --ignore-scripts

COPY . .

RUN npm run build

# ---------------------------------------------------------------------------
# Minimal runtime (only the Nitro server output)
# ---------------------------------------------------------------------------
FROM node:22-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV HOST=0.0.0.0
ENV PORT=3000
ENV NUXT_TELEMETRY_DISABLED=1

COPY --from=build /app/.output ./.output

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
