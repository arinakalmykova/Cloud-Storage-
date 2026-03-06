import http from 'k6/http';
import { check, sleep } from 'k6';
import { htmlReport } from 'https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js';
import { textSummary } from 'https://jslib.k6.io/k6-summary/0.0.1/index.js';

const BASE_URL = 'http://localhost:8000/api';
const RPS = 10;
const DURATION = '5m';

export const options = {
  stages: [
    { duration: '30s', target: RPS },
    { duration: DURATION, target: RPS },
    { duration: '30s', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<300'],
    http_req_failed: ['rate<0.01'],
    checks: ['rate>0.99'],
  },
};


const headers = {
  'Content-Type': 'application/json',
  'Authorization': `Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJlZjAxZWE1MS00M2RmLTRiMTAtOTFiOS05ZDc0ZDA4MDJhNDAiLCJlbWFpbCI6ImFyaWNyYXRlQGdtYWlsLmNvbSIsImlhdCI6MTc3MjAxMDIzNywiZXhwIjoxNzcyMDEzODM3fQ.G0P-PrHoIk3ZHIvKMag4_zN65ZLeGDidXcqZtqSNO8g`
};

export default function () {

  // Получение последних фото
  let recent = http.get(`${BASE_URL}/photos/recent`, { headers });
  check(recent, {
    'recent status 200': (r) => r.status === 200,
  });

  // Поиск фото
  let search = http.get(`${BASE_URL}/photos/search?query=test`, { headers });
  check(search, {
    'search status 200': (r) => r.status === 200,
  });

  // Создание папки
  let folderRes = http.post(`${BASE_URL}/folders`, JSON.stringify({
    name: `Folder_${Date.now()}`
  }), { headers });

  check(folderRes, {
    'folder created': (r) => r.status === 201 || r.status === 200,
  });

  // Получение списка папок
  let folders = http.get(`${BASE_URL}/folders`, { headers });
  check(folders, {
    'folders status 200': (r) => r.status === 200,
  });

  // Получение URL загрузки
  let uploadUrl = http.post(`${BASE_URL}/photos/upload-url`, JSON.stringify({
    filename: `photo_${Date.now()}.jpg`
  }), { headers });

  check(uploadUrl, {
    'upload-url ok': (r) => r.status === 200 || r.status === 201,
  });


    // Получаем фото по ID
    let photoId = 1;

    let show = http.get(`${BASE_URL}/photos/${photoId}`, { headers });
    check(show, { 'photo loaded': (r) => r.status === 200 });

    // Переименование
    let rename = http.put(`${BASE_URL}/photos/${photoId}`, JSON.stringify({
    name: 'Renamed Photo'
    }), { headers });

    check(rename, { 'photo renamed': (r) => r.status === 200 });

    // Добавление тегов
    let tags = http.post(`${BASE_URL}/photos/${photoId}/tags`, JSON.stringify({
    tags: ['nature', 'summer']
    }), { headers });

    check(tags, { 'tags updated': (r) => r.status === 200 });

    // Удаление
    let del = http.del(`${BASE_URL}/photos/${photoId}`, null, { headers });
    check(del, { 'photo deleted': (r) => r.status === 200 });

  sleep(1);
}

export function handleSummary(data) {
  return {
    'k6-report.html': htmlReport(data),
    stdout: textSummary(data, { indent: ' ', enableColors: true }),
  };
}