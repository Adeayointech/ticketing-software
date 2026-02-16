import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { organizerAPI, eventsAPI } from '../services/api';
import './OrganizerDashboard.css';

const OrganizerDashboard = () => {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      setLoading(true);
      const response = await organizerAPI.getMyEvents();
      setEvents(response.data.data.events);
    } catch (err) {
      setError('Failed to load events');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (eventId, eventTitle) => {
    if (window.confirm(`Are you sure you want to delete "${eventTitle}"? This action cannot be undone.`)) {
      try {
        await eventsAPI.delete(eventId);
        setEvents(events.filter(event => event.id !== eventId));
        setError('');
      } catch (err) {
        setError(err.response?.data?.message || 'Failed to delete event');
      }
    }
  };

  const handleEdit = (eventId) => {
    navigate(`/organizer/edit-event/${eventId}`);
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'published':
        return 'status-published';
      case 'draft':
        return 'status-draft';
      case 'cancelled':
        return 'status-cancelled';
      case 'completed':
        return 'status-completed';
      default:
        return '';
    }
  };

  if (loading) {
    return <div className="container loading">Loading events...</div>;
  }

  return (
    <div className="organizer-dashboard-page">
      <div className="container">
        <div className="dashboard-header">
          <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: 20 }}>
            <Link to="/organizer/scan-ticket" className="scan-ticket-card" style={{
              display: 'block',
              background: '#f8f9fa',
              border: '2px solid #007bff',
              borderRadius: 12,
              padding: 24,
              color: '#007bff',
              fontWeight: 600,
              fontSize: 20,
              textAlign: 'center',
              textDecoration: 'none',
              boxShadow: '0 2px 12px rgba(0,0,0,0.08)',
              width: 260,
              marginRight: 0
            }}>
              <div style={{ fontSize: 40, marginBottom: 10 }}>📷</div>
              Scan Tickets
              <div style={{ fontSize: 14, color: '#333', fontWeight: 400, marginTop: 8 }}>Validate entry at the event</div>
            </Link>
          </div>
          <div>
            <h1>My Events</h1>
            <p>Manage your events and view statistics</p>
          </div>
          <Link to="/organizer/create-event" className="btn btn-primary">
            + Create Event
          </Link>
        </div>

        {error && <div className="alert alert-error">{error}</div>}

        {events.length === 0 ? (
          <div className="no-events">
            <div className="empty-state">
              <span className="empty-icon">📅</span>
              <h2>No Events Yet</h2>
              <p>Create your first event to get started</p>
              <Link to="/organizer/create-event" className="btn btn-primary">
                Create Event
              </Link>
            </div>
          </div>
        ) : (
          <div className="events-table-container">
            <table className="events-table">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Tickets Sold</th>
                  <th>Revenue</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {events.map((event) => (
                  <tr key={event.id}>
                    <td>
                      <div className="event-cell">
                        {event.image_url && (
                          <img src={event.image_url} alt={event.title} className="event-thumb" />
                        )}
                        <div>
                          <div className="event-title">{event.title}</div>
                          <div className="event-venue">{event.venue}</div>
                        </div>
                      </div>
                    </td>
                    <td>{formatDate(event.event_date)}</td>
                    <td>
                      <span className={`event-status ${getStatusColor(event.status)}`}>
                        {event.status}
                      </span>
                    </td>
                    <td>{event.tickets_sold || 0}</td>
                    <td>${parseFloat(event.total_revenue || 0).toFixed(2)}</td>
                    <td>
                      <div className="action-buttons">
                        <Link to={`/events/${event.id}`} className="btn-action btn-view">
                          👁️ View
                        </Link>
                        <button 
                          onClick={() => handleEdit(event.id)} 
                          className="btn-action btn-edit"
                        >
                          ✏️ Edit
                        </button>
                        {(!event.tickets_sold || event.tickets_sold === 0) && (
                          <button 
                            onClick={() => handleDelete(event.id, event.title)} 
                            className="btn-action btn-delete"
                          >
                            🗑️ Delete
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default OrganizerDashboard;
