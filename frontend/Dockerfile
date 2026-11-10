# syntax=docker/dockerfile:1

# ---- Stage 1: build the Vite/React SPA -------------------------------------------------
FROM node:20-alpine AS build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

# Vite bakes VITE_* vars into the JS bundle at build time, so the backend API
# URL has to be supplied as a build arg (set it in Dokploy's build settings).
ARG VITE_API_BASE_URL=/api/v1
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL

# The Google OAuth Client ID is public by design (it identifies the app to
# Google, not a secret), but like the API URL above it's baked in at build
# time, so it also has to come in as a build arg.
ARG VITE_GOOGLE_CLIENT_ID=
ENV VITE_GOOGLE_CLIENT_ID=$VITE_GOOGLE_CLIENT_ID

RUN npm run build

# ---- Stage 2: serve the static build with nginx ----------------------------------------
FROM nginx:1.27-alpine AS runtime

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
