import React, { useState } from 'react';
import { login, Storage } from '../api';

const Login = ({ onLogin }) => {
    const [username, setUsername] = useState('admin');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        try {
            // Try to login normally
            const res = await login(username, 'nopassword');
            if (res.data.ok) {
                onLogin(res.data.username);
            }
        } catch (err) {
            // Offline Fallback: If server is unreachable, let them in anyway
            console.log('Server unreachable, using offline mode');
            try {
                await Storage.set('token', 'OFFLINE_ACCESS_TOKEN');
                onLogin('Admin (Offline)');
            } catch (storageErr) {
                console.error('Storage error:', storageErr);
                // Even if storage fails, let them in
                onLogin('Admin (Offline)');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="login-container">
            <h2>Kapdafactory Admin</h2>
            <form onSubmit={handleSubmit}>
                <input
                    type="text"
                    placeholder="Username"
                    value={username}
                    onChange={(e) => setUsername(e.target.value)}
                    required
                />
                {/* Password removed as requested */}
                <button type="submit" disabled={loading}>
                    {loading ? 'Entering...' : 'Enter App'}
                </button>
                {error && <p className="error">{error}</p>}
            </form>
        </div>
    );
};

export default Login;
