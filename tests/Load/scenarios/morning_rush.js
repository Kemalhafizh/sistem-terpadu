import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 200 }, // ramp-up to 200 users
        { duration: '3m', target: 730 }, // ramp-up and stay at 730 users (teachers + students)
        { duration: '1m', target: 0 },   // ramp-down to 0
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500'], // 95% of requests should be below 1.5 seconds
        http_req_failed: ['rate<0.005'],    // error rate less than 0.5%
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

export default function () {
    // Determine user role randomly (95% students, 5% teachers)
    const isTeacher = Math.random() < 0.05;

    if (isTeacher) {
        // Teacher simulation: Log attendance
        // 1. View attendance dashboard
        let res = http.get(`${BASE_URL}/attendances`);
        check(res, {
            'teacher view attendances status is 200': (r) => r.status === 200,
        });
        sleep(Math.random() * 2 + 1);

        // 2. Post attendance records (mock post request)
        let payload = JSON.stringify({
            classroom_id: 1,
            date: new Date().toISOString().split('T')[0],
            students: [
                { student_id: 1, status: 'H' },
                { student_id: 2, status: 'H' },
                { student_id: 3, status: 'S' },
                { student_id: 4, status: 'A' }
            ]
        });
        let params = { headers: { 'Content-Type': 'application/json' } };
        res = http.post(`${BASE_URL}/attendances`, payload, params);
        check(res, {
            'attendance post status is 200 or 302': (r) => r.status === 200 || r.status === 302,
        });
        sleep(Math.random() * 3 + 3);
    } else {
        // Student simulation: Login and view home dashboard
        // 1. Visit dashboard
        let res = http.get(`${BASE_URL}/dashboard`);
        check(res, {
            'student view dashboard status is 200': (r) => r.status === 200,
        });
        sleep(Math.random() * 3 + 2);

        // 2. Load my academic info
        res = http.get(`${BASE_URL}/my-academic`);
        check(res, {
            'student view academic status is 200': (r) => r.status === 200,
        });
        sleep(Math.random() * 3 + 3);
    }
}
