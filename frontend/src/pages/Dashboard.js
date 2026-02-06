import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import './Dashboard.css';

const Dashboard = () => {
  const { user, isOrganizer } = useAuth();

  return (
    <div className="dashboard-page">
      <div className="container">
        <div className="dashboard-header">
          <h1>Welcome, {user?.first_name}!</h1>
          <p className="user-role">
            Account Type: <span>{isOrganizer ? 'Organizer' : 'Attendee'}</span>
          </p>
        </div>

        <div className="dashboard-grid">
          {isOrganizer ? (
            <>
              <Link to="/organizer/dashboard" className="dashboard-card">
                <div className="card-icon">📊</div>
                <h3>My Events</h3>
                <p>View and manage your events</p>
              </Link>

              <Link to="/organizer/create-event" className="dashboard-card">
                <div className="card-icon">➕</div>
                <h3>Create Event</h3>
                <p>Add a new event</p>
              </Link>

              <Link to="/organizer/scan-ticket" className="dashboard-card" style={{ border: '2px solid #007bff', color: '#007bff', fontWeight: 600 }}>
                <div className="card-icon" style={{ fontSize: 36 }}>📷</div>
                <h3>Scan Tickets</h3>
                <p>Validate entry at the event</p>
              </Link>
           </>
          ) : (
            <>
              <Link to="/my-tickets" className="dashboard-card">
                <div className="card-icon">🎟️</div>
                <h3>My Tickets</h3>
                <p>View your purchased tickets</p>
              </Link>

              <Link to="/my-orders" className="dashboard-card">
                <div className="card-icon">📦</div>
                <h3>My Orders</h3>
                <p>View your order history</p>
              </Link>

              <Link to="/" className="dashboard-card">
                <div className="card-icon">🎭</div>
                <h3>Browse Events</h3>
                <p>Find exciting events</p>
              </Link>
            </>
          )}

          <div className="dashboard-card info-card">
            <div className="card-icon">👤</div>
            <h3>Profile</h3>
            <div className="profile-info">
              <p><strong>Name:</strong> {user?.first_name} {user?.last_name}</p>
              <p><strong>Email:</strong> {user?.email}</p>
              {user?.phone && <p><strong>Phone:</strong> {user.phone}</p>}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;
