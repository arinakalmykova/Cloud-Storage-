# ==========================
# Stage 1: сборка фронтенда
# ==========================
FROM node:20-alpine AS build

WORKDIR /app

# Копируем package.json и package-lock.json (или yarn.lock)
COPY package*.json ./

# Устанавливаем зависимости
RUN npm install
RUN npm install --save-dev @types/node
# Копируем остальной код
COPY . .

# Собираем проект — создастся папка dist
RUN npm run build

# ==========================
# Stage 2: nginx для деплоя
# ==========================
FROM nginx:alpine

# Копируем собранные файлы из dist
COPY --from=build /app/dist /usr/share/nginx/html

# Открываем порт 80
EXPOSE 80

# Запускаем nginx
CMD ["nginx", "-g", "daemon off;"]
