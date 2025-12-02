import React, { useState, useEffect } from 'react';
import Login from './components/Login';
import Upload from './components/Upload';
import Search from './components/Search';
import { logout, checkAuth } from './api';

import { App as CapacitorApp } from '@capacitor/app';
import QueueService from './services/QueueService';



function App() {
  const [user, setUser] = useState(null);

  useEffect(() => {
    // Check for existing session
    const checkSession = async () => {
      const isAuthenticated = await checkAuth();
      if (isAuthenticated) {
        setUser('Admin'); // Restore session
      }
    };
    checkSession();

    // Background Task Listener
    CapacitorApp.addListener('appStateChange', ({ isActive }) => {
      if (isActive) {
        QueueService.processQueue();
      } else {
        // App went to background
        // We can try to keep processing or register a background task if using the runner
        // For MVP, just triggering processQueue on resume is good, 
        // and maybe triggering it once on background if supported.
        QueueService.processQueue();
      }
    });
  }, []);
  const [tab, setTab] = useState('upload'); // 'upload' or 'search'

  const handleLogout = async () => {
    await logout();
    setUser(null);
  };

  if (!user) {
    return <Login onLogin={setUser} />;
  }

  return (
    <div className="app-container">
      <header>
        <div className="logo-section">
          <img src="/logo.png" alt="Kapdafactory Logo" className="app-logo" />
          <h1>Kapdafactory</h1>
        </div>
        <button onClick={handleLogout} className="logout-btn">Logout</button>
      </header>

      <nav>
        <button
          className={tab === 'upload' ? 'active' : ''}
          onClick={() => setTab('upload')}
        >
          Upload
        </button>
        <button
          className={tab === 'search' ? 'active' : ''}
          onClick={() => setTab('search')}
        >
          Search
        </button>
      </nav>

      <main>
        {tab === 'upload' ? <Upload /> : <Search />}
      </main>
    </div>
  );
}

export default App;
