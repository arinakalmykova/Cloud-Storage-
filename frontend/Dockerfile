# ==========================
# Stage 1: сборка фронтенда
# ==========================
FROM node:20-alpine AS build

WORKDIR /app

# 1. Копируем package файлы
COPY package*.json ./

# 2. Устанавливаем ВСЕ зависимости (включая dev)
RUN npm ci --include=dev

# 3. Копируем ВСЕ файлы проекта
COPY . .

# 3.1. Явно проверим tsconfig.json
RUN ls -la /app/tsconfig.app.json /app/src

# 4. Собираем проект (tsc + vite)
RUN npx tsc --noEmit  # проверка TypeScript
RUN npm run build

# ==========================
# Stage 2: nginx для деплоя
# ==========================
FROM nginx:alpine

# Копируем собранные файлы из dist
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/app.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
