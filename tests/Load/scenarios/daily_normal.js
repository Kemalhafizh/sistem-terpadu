import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 50 },  // ramp-up to 50 users
        { duration: '3m', target: 150 }, // stay at 150 users (daily normal load)
        { duration: '1m', target: 0 },   // ramp-down to 0
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% of requests should be below 500ms
        http_req_failed: ['rate<0.001'],   // error rate less than 0.1%
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

export default function () {
    // 1. Visit landing page
    let res = http.get(`${BASE_URL}/`);
    check(res, {
        'landing page status is 200': (r) => r.status === 200,
        'contains SMA Sultan Agung': (r) => r.body.includes('Sultan Agung') || r.body.includes('SMA'),
    });
    sleep(Math.random() * 2 + 1); // think time 1-3s

    // 2. Load public calendar
    res = http.get(`${BASE_URL}/calendar`);
    check(res, {
        'calendar page status is 200': (r) => r.status === 200,
    });
    sleep(Math.random() * 3 + 2); // think time 2-5s
}
