import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 100 }, // ramp-up to 100 users
        { duration: '3m', target: 500 }, // ramp-up and stay at 500 users (parents)
        { duration: '1m', target: 0 },   // ramp-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'], // 95% of requests should complete under 3 seconds
        http_req_failed: ['rate<0.001'],   // error rate less than 0.1% (billing must be highly stable)
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

export default function () {
    // 1. Parent loads parent-portal dashboard
    let res = http.get(`${BASE_URL}/parent-portal`);
    check(res, {
        'parent portal dashboard status is 200': (r) => r.status === 200,
    });
    sleep(Math.random() * 3 + 2); // think time 2-5s

    // 2. Parent checks children's billing details
    // Assuming child student_id is 1
    res = http.get(`${BASE_URL}/parent-portal/child/1/billings`);
    check(res, {
        'billing details page status is 200': (r) => r.status === 200,
        'contains billing item': (r) => r.body.includes('SPP') || r.body.includes('Tagihan') || r.body.includes('Billing'),
    });
    sleep(Math.random() * 4 + 3); // think time 3-7s

    // 3. Parent triggers checkout mock/Midtrans payment creation
    // Assuming billing_id is 1
    res = http.get(`${BASE_URL}/parent-portal/billing/1/checkout?method=bank_transfer&bank=bca`);
    check(res, {
        'checkout status is 200 or 302': (r) => r.status === 200 || r.status === 302,
    });
    sleep(Math.random() * 5 + 5); // think time 5-10s
}
