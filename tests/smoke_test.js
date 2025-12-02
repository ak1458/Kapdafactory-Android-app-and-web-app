const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');
const path = require('path');

const API_URL = 'http://localhost:8000/api';

async function runSmokeTest() {
    try {
        console.log('1. Logging in...');
        const loginRes = await axios.post(`${API_URL}/login.php`, {
            username: 'admin',
            password: 'password123'
        });

        if (!loginRes.data.ok || !loginRes.data.token) {
            console.log('Login Response:', loginRes.data);
            throw new Error('Login failed');
        }
        const token = loginRes.data.token;
        console.log('   Success! Token:', token.substring(0, 10) + '...');

        console.log('2. Uploading Measurement...');
        const form = new FormData();
        form.append('token', 'SMOKE_TEST_' + Date.now());
        form.append('measurement_text', 'Automated smoke test');
        form.append('expected_delivery', '2025-12-31');

        // Use a dummy file or create one
        const dummyPath = path.join(__dirname, 'test_image.txt');
        fs.writeFileSync(dummyPath, 'dummy image content');
        form.append('image', fs.createReadStream(dummyPath), 'test.jpg');

        const headers = {
            ...form.getHeaders(),
            'Authorization': `Bearer ${token}`
        };
        console.log('Sending Headers:', headers);

        const uploadRes = await axios.post(`${API_URL}/measurements.php`, form, {
            headers: headers
        });

        if (!uploadRes.data.ok) {
            throw new Error('Upload failed');
        }

        console.log('   Success! DB ID:', uploadRes.data.id);
        console.log('   Image Path:', uploadRes.data.image_path);

        // Cleanup
        fs.unlinkSync(dummyPath);

    } catch (error) {
        console.error('Test Failed:', error.response ? error.response.data : error.message);
        process.exit(1);
    }
}

runSmokeTest();
