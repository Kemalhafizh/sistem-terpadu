import http from 'k6/http';
import { sleep, check } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 50 },  // ramp-up to 50 users
        { duration: '3m', target: 200 }, // stay at 200 users (students taking exam)
        { duration: '1m', target: 0 },   // ramp-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% of requests should be below 2 seconds
        http_req_failed: ['rate===0'],      // zero error tolerance (must be 0% error)
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost';

export default function () {
    // 1. Student loads active exam session
    // Assuming exam_id is 1
    let res = http.get(`${BASE_URL}/elearning/assignment/1/secure`);
    check(res, {
        'exam portal load status is 200': (r) => r.status === 200,
    });
    sleep(Math.random() * 5 + 5); // reading the question (5-10s)

    // 2. Student submits an answer for a question
    let payload = JSON.stringify({
        question_id: Math.floor(Math.random() * 10) + 1,
        selected_option: ['A', 'B', 'C', 'D'][Math.floor(Math.random() * 4)],
        duration_spent: 12
    });
    let params = { headers: { 'Content-Type': 'application/json' } };
    
    res = http.post(`${BASE_URL}/elearning/assignment/1/submit`, payload, params);
    check(res, {
        'answer submission status is 200': (r) => r.status === 200,
    });
    sleep(Math.random() * 10 + 10); // next question read time (10-20s)
}
