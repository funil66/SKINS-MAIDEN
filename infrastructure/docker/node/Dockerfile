# Multi-stage build for Vue.js frontend
FROM node:18-alpine AS base
WORKDIR /app

# Stage 2: Instalação de dependências
FROM base AS deps
COPY package.json package-lock.json ./
RUN npm ci

# Stage 3: Build da aplicação
FROM base AS builder
COPY --from=deps /app/node_modules /app/node_modules
COPY . .
RUN npm run build

# Stage 4: Imagem de Desenvolvimento
FROM base AS development
CMD ["npm", "run", "dev"]

# Stage 5: Imagem de Produção
FROM nginx:stable-alpine AS production
COPY --from=builder /app/dist /usr/share/nginx/html
COPY infrastructure/docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
