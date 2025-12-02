import { Network } from '@capacitor/network';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { uploadMeasurement } from '../api';

const QUEUE_KEY = 'upload_queue';

class QueueService {
    constructor() {
        this.queue = JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]');
        this.isProcessing = false;
        this.initNetworkListener();
    }

    b64toBlob(b64Data, contentType = '', sliceSize = 512) {
        const byteCharacters = atob(b64Data);
        const byteArrays = [];

        for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            const slice = byteCharacters.slice(offset, offset + sliceSize);
            const byteNumbers = new Array(slice.length);
            for (let i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            byteArrays.push(byteArray);
        }

        const blob = new Blob(byteArrays, { type: contentType });
        return blob;
    }

    async initNetworkListener() {
        Network.addListener('networkStatusChange', status => {
            if (status.connected) {
                this.processQueue();
            }
        });
    }

    saveQueue() {
        localStorage.setItem(QUEUE_KEY, JSON.stringify(this.queue));
    }

    addJob(jobData) {
        const job = {
            id: Date.now(),
            data: jobData, // { token, measurement_text, expected_delivery, imagePath }
            attempts: 0,
            status: 'pending',
            timestamp: Date.now()
        };
        this.queue.push(job);
        this.saveQueue();
        this.processQueue();
        return job.id;
    }

    async processQueue() {
        if (this.isProcessing) return;

        const status = await Network.getStatus();
        if (!status.connected) return;

        this.isProcessing = true;

        // Find first pending job
        const jobIndex = this.queue.findIndex(j => j.status === 'pending');
        if (jobIndex === -1) {
            this.isProcessing = false;
            return;
        }

        const job = this.queue[jobIndex];

        try {
            // Read file from Filesystem
            const fileData = await Filesystem.readFile({
                path: job.data.imagePath,
                directory: Directory.Data
            });

            // Convert base64 to Blob
            const blob = this.b64toBlob(fileData.data, 'image/jpeg');

            const formData = new FormData();
            formData.append('token', job.data.token);
            formData.append('image', blob, 'upload.jpg');
            formData.append('measurement_text', job.data.measurement_text);
            formData.append('expected_delivery', job.data.expected_delivery);

            await uploadMeasurement(formData);

            // Mark success
            this.queue.splice(jobIndex, 1);
            this.saveQueue();

            // Process next
            this.isProcessing = false;
            this.processQueue();

        } catch (err) {
            console.error('Upload failed', err);
            job.attempts++;
            if (job.attempts >= 5) {
                job.status = 'failed'; // Dead letter
            }
            this.saveQueue();
            this.isProcessing = false;
        }
    }

    getQueue() {
        return this.queue;
    }
}

export default new QueueService();
