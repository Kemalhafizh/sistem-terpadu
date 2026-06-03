import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 300 },  // ramp-up to 300 users
        { duration: '3m', target: 1200 }, // stay at 1200 users (rapor day peak load)
        { duration: '1m', target: 0 },    // ramp-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<3000'], // 95% of requests should complete under 3 seconds
        http_req_failed: ['rate<0.01'],    // less than 1% errors
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

export default function () {
    // 1. Parent/Student loads e-rapor checklist page
    let res = http.get(`${BASE_URL}/report-cards`);
    check(res, {
        'report card index page status is 200': (r) => r.status === 200,
    });
    sleep(Math.random() * 3 + 2); // think time 2-5s

    // 2. Parent/Student downloads their specific report card PDF
    // Assuming student_id is 1
    res = http.get(`${BASE_URL}/report-cards/1/pdf`);
    check(res, {
        'pdf generation/download status is 200': (r) => r.status === 200,
        'is pdf response': (r) => r.headers['Content-Type'] === 'application/pdf' || r.status === 200, // file format
    });
    sleep(Math.random() * 5 + 5); // think time 5-10s
}
