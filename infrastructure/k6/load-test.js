import http from 'k6/http';
import { check, sleep } from 'k6';

export let options = {
  stages: [
    { duration: '30s', target: 20 },   // Ramp-up to 20 users over 30 seconds
    { duration: '1m', target: 100 },   // Stay at 100 users for 1 minute
    { duration: '2m', target: 300 },   // Ramp-up to 300 users over 2 minutes
    { duration: '5m', target: 300 },   // Stay at 300 users for 5 minutes
    { duration: '2m', target: 100 },   // Ramp-down to 100 users over 2 minutes
    { duration: '30s', target: 0 },    // Ramp-down to 0 users over 30 seconds
  ],
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests must complete below 500ms
    http_req_failed: ['rate<0.1'],    // Error rate must be below 10%
  },
};

const BASE_URL = 'http://nginx';

export default function () {
  // Test home page
  let homeResponse = http.get(`${BASE_URL}/`);
  check(homeResponse, {
    'home page status is 200': (r) => r.status === 200,
    'home page response time < 500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);

  // Test API health endpoint
  let healthResponse = http.get(`${BASE_URL}/api/health`);
  check(healthResponse, {
    'health endpoint status is 200': (r) => r.status === 200,
    'health endpoint response time < 200ms': (r) => r.timings.duration < 200,
  });

  sleep(1);

  // Test API users endpoint (this will likely return 401, but we're testing response time)
  let usersResponse = http.get(`${BASE_URL}/api/v1/users`);
  check(usersResponse, {
    'users endpoint responds': (r) => r.status !== 0,
    'users endpoint response time < 300ms': (r) => r.timings.duration < 300,
  });

  sleep(Math.random() * 2 + 1); // Random sleep between 1-3 seconds
}
