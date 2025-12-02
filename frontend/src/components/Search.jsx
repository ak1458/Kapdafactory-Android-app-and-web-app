import React, { useState } from 'react';
import { searchMeasurement, updateMeasurement } from '../api';

const Search = () => {
    const [token, setToken] = useState('');
    const [result, setResult] = useState(null);
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const [editing, setEditing] = useState(false);

    // Edit state
    const [editText, setEditText] = useState('');
    const [editDate, setEditDate] = useState('');
    const [editStatus, setEditStatus] = useState('');

    const handleSearch = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError('');
        setResult(null);
        setEditing(false);

        try {
            const res = await searchMeasurement(token);
            setResult(res.data);
            // Init edit state
            setEditText(res.data.measurement_text || '');
            setEditDate(res.data.expected_delivery || '');
            setEditStatus(res.data.status || 'pending');
        } catch (err) {
            setError(err.response?.data?.error || 'Not found');
        } finally {
            setLoading(false);
        }
    };

    const handleUpdate = async () => {
        if (!result) return;
        setLoading(true);
        try {
            const data = {
                measurement_text: editText,
                expected_delivery: editDate,
                status: editStatus
            };
            await updateMeasurement(result.id, data);
            setResult({ ...result, ...data });
            setEditing(false);
            alert('Updated successfully');
        } catch (err) {
            alert('Update failed: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    const handleMarkDelivered = async () => {
        if (!result) return;
        if (!window.confirm('Are you sure you want to mark this as Delivered?')) return;

        setLoading(true);
        try {
            const data = {
                status: 'delivered'
            };
            await updateMeasurement(result.id, data);
            setResult({ ...result, status: 'delivered' });
            alert('Marked as Delivered');
        } catch (err) {
            alert('Update failed: ' + (err.response?.data?.error || err.message));
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="search-container">
            <h3>Search Record</h3>
            <form onSubmit={handleSearch} className="search-form">
                <input
                    type="text"
                    value={token}
                    onChange={(e) => setToken(e.target.value)}
                    placeholder="Enter Token"
                    required
                />
                <button type="submit" disabled={loading} className="submit-btn" style={{ marginTop: '1rem' }}>
                    {loading ? 'Searching...' : 'Search'}
                </button>
            </form>

            {error && <p className="error">{error}</p>}

            {result && (
                <div className="result-card">
                    <div className="image-container">
                        <a href={result.image_url} target="_blank" rel="noreferrer">
                            <img src={result.image_url} alt="Measurement" />
                        </a>
                    </div>

                    {editing ? (
                        <div className="edit-form">
                            <textarea value={editText} onChange={e => setEditText(e.target.value)} />
                            <input type="date" value={editDate} onChange={e => setEditDate(e.target.value)} />
                            <select value={editStatus} onChange={e => setEditStatus(e.target.value)}>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="delivered">Delivered</option>
                            </select>
                            <div className="actions">
                                <button onClick={handleUpdate} disabled={loading}>Save</button>
                                <button onClick={() => setEditing(false)} className="cancel">Cancel</button>
                            </div>
                        </div>
                    ) : (
                        <div className="details">
                            <p><strong>Token:</strong> {result.token}</p>
                            <p><strong>Status:</strong> <span className={`badge ${result.status}`}>{result.status}</span></p>
                            <p><strong>Date:</strong> {result.expected_delivery}</p>
                            <p className="text-content">{result.measurement_text}</p>
                            <div className="action-buttons">
                                <button onClick={() => setEditing(true)} className="edit-btn">Edit</button>
                                {result.status !== 'delivered' && (
                                    <button onClick={handleMarkDelivered} className="deliver-btn" style={{ marginLeft: '10px', backgroundColor: '#28a745' }}>
                                        Mark as Delivered
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default Search;
