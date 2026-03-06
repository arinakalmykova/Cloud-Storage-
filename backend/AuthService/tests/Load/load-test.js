import http from "k6/http";
import { check } from "k6";
import { htmlReport } from "https://raw.githubusercontent.com/benc-uk/k6-reporter/main/dist/bundle.js";

export const options = {
    vus: 1000,
    iterations: 1000,
};

const BASE_URL = "http://localhost:80";

export default function () {
    const userId = __VU;
    const email = `user_${userId}_${Date.now()}@test.com`;

    const payload = JSON.stringify({
        name: `User ${userId}`,
        email: email,
        password: "Test123456",
    });

    console.log(`Пользователь ${userId} отправляет запрос...`);

    const start = Date.now();
    const res = http.post(`${BASE_URL}/api/auth/register`, payload, {
        headers: { "Content-Type": "application/json" },
        timeout: "60s",
    });
    const duration = Date.now() - start;

    if (res.status === 200 || res.status === 201) {
        console.log(`✅ Пользователь ${userId}: УСПЕХ за ${duration}ms`);
        try {
            const data = JSON.parse(res.body);
            console.log(`   ID: ${data.userId || data.id}`);
        } catch (e) {}
    } else if (res.status === 0) {
        console.log(`❌ Пользователь ${userId}: ТАЙМАУТ за ${duration}ms`);
    } else {
        console.log(
            `❌ Пользователь ${userId}: ОШИБКА ${res.status} за ${duration}ms`,
        );
    }

    check(res, {
        "регистрация успешна": (r) => r.status === 200 || r.status === 201,
    });
}

export function handleSummary(data) {
    const total = data.metrics.http_reqs.values.count;

    let success = 0;
    if (data.metrics.checks) {
        const checkValue = data.metrics.checks.values.find(
            (c) => c.name === "регистрация успешна",
        );
        success = checkValue ? checkValue.passes : 0;
    }

    console.log("\n" + "=".repeat(50));
    console.log("📊 ТЕСТ: 2 ПОЛЬЗОВАТЕЛЯ ОДНОВРЕМЕННО");
    console.log("=".repeat(50));
    console.log(`\n✅ Успешно: ${success} из ${total}`);
    console.log(`❌ Ошибок: ${total - success}`);

    return {
        "test-2-users.html": htmlReport(data),
    };
}
