# ==========================
# Stage 1: сборка фронтенда
# ==========================
FROM node:20-alpine AS build

WORKDIR /app

# 1. Копируем ТОЛЬКО package файлы
COPY package*.json ./

# 2. Устанавливаем ВСЕ зависимости (включая dev)
RUN npm ci --include=dev

# 3. Копируем ВСЕ файлы (включая конфиги линтеров)
COPY . .

# 4. Запускаем линтинг (опционально, можно пропустить в билде)
# RUN npm run lint:check

# 5. Собираем проект
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