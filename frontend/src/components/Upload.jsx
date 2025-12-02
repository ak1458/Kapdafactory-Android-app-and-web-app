import React, { useState } from 'react';
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { uploadMeasurement } from '../api';

const Upload = () => {
    const [token, setToken] = useState('');
    const [measurement, setMeasurement] = useState('');
    const [deliveryDate, setDeliveryDate] = useState('');
    const [imagePath, setImagePath] = useState(null); // Native path
    const [previewUrl, setPreviewUrl] = useState(null); // Webview friendly path
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');

    const handleCapture = async () => {
        try {
            const photo = await Camera.getPhoto({
                quality: 70,
                allowEditing: false,
                resultType: CameraResultType.Uri,
                source: CameraSource.Camera,
                width: 1280
            });

            // Save to filesystem (permanent storage)
            const savedFile = await savePicture(photo);
            setImagePath(savedFile.filepath);
            setPreviewUrl(savedFile.webviewPath);
        } catch (error) {
            console.error('Camera error:', error);
            setMessage('Camera cancelled or failed');
        }
    };

    const savePicture = async (photo) => {
        const base64Data = await readAsBase64(photo);
        const fileName = new Date().getTime() + '.jpeg';
        const savedFile = await Filesystem.writeFile({
            path: fileName,
            data: base64Data,
            directory: Directory.Data
        });

        return {
            filepath: fileName,
            webviewPath: photo.webPath
        };
    };

    const readAsBase64 = async (photo) => {
        // Fetch the photo, read as a blob, then convert to base64
        const response = await fetch(photo.webPath);
        const blob = await response.blob();
        return await convertBlobToBase64(blob);
    };

    const convertBlobToBase64 = (blob) => new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onerror = reject;
        reader.onload = () => {
            resolve(reader.result);
        };
        reader.readAsDataURL(blob);
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!token || !imagePath) {
            setMessage('Token and Image are required');
            return;
        }

        setLoading(true);
        try {
            // Direct Upload (No Queue)
            const response = await fetch(previewUrl);
            const blob = await response.blob();

            const formData = new FormData();
            formData.append('token', token);
            formData.append('image', blob, 'upload.jpg');
            formData.append('measurement_text', measurement);
            formData.append('expected_delivery', deliveryDate);

            await uploadMeasurement(formData);

            setMessage('✓ Uploaded Successfully!');
            setToken('');
            setMeasurement('');
            setDeliveryDate('');
            setImagePath(null);
            setPreviewUrl(null);
        } catch (err) {
            console.error(err);
            setMessage('Upload Failed: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="upload-container">
            <h2>New Measurement</h2>
            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label>Token ID</label>
                    <input
                        type="text"
                        value={token}
                        onChange={(e) => setToken(e.target.value.toUpperCase())}
                        placeholder="e.g. A101"
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Photo</label>
                    <div className="image-preview" onClick={handleCapture}>
                        {previewUrl ? (
                            <img src={previewUrl} alt="Preview" />
                        ) : (
                            <div className="placeholder">
                                <span>Tap to Capture</span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="form-group">
                    <label>Measurements</label>
                    <textarea
                        value={measurement}
                        onChange={(e) => setMeasurement(e.target.value)}
                        placeholder="Enter details..."
                        rows="4"
                    />
                </div>

                <div className="form-group">
                    <label>Expected Delivery</label>
                    <input
                        type="date"
                        value={deliveryDate}
                        onChange={(e) => setDeliveryDate(e.target.value)}
                    />
                </div>

                <button type="submit" disabled={loading} className="submit-btn">
                    {loading ? 'Saving...' : 'Save Record'}
                </button>

                {message && <p className={`status ${message.includes('saved') ? 'success' : 'error'}`}>{message}</p>}
            </form>
        </div>
    );
};

export default Upload;
