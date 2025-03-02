# Używamy Node.js 18 jako bazowego obrazu
FROM node:18

# Ustawiamy katalog roboczy w kontenerze
WORKDIR /app

# Kopiujemy pliki package.json i package-lock.json, aby szybciej instalować zależności
COPY package.json package-lock.json ./

# Instalujemy zależności
RUN npm install

# Kopiujemy resztę plików projektu
COPY . .

# Eksponujemy port 5173 (Vite domyślnie działa na tym porcie)
EXPOSE 5173

# Uruchamiamy aplikację Vite
CMD ["npm", "run", "dev"]
