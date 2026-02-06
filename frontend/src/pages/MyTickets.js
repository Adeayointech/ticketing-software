import { QRCodeSVG } from 'qrcode.react';
import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';


import { ticketsAPI } from '../services/api';
import './MyTickets.css';

const MyTickets = () => {
  const { orderId } = useParams();
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchTickets();
  }, []);

  const fetchTickets = async () => {
    try {
      setLoading(true);
      const response = await ticketsAPI.getMyTickets();
      let allTickets = response.data.data.tickets;
      if (orderId) {
        allTickets = allTickets.filter(ticket => String(ticket.order_id) === String(orderId));
      }
      setTickets(allTickets);
    } catch (err) {
      setError('Failed to load tickets');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusClass = (status) => {
    switch (status) {
      case 'valid':
        return 'status-valid';
      case 'used':
        return 'status-used';
      case 'cancelled':
        return 'status-cancelled';
      default:
        return '';
    }
  };

  if (loading) {
    return <div className="container loading">Loading tickets...</div>;
  }

  return (
    <div className="my-tickets-page">
      <div className="container">
        <h1 className="page-title">My Tickets</h1>
        {error && <div className="alert alert-error">{error}</div>}
        {tickets.length === 0 ? (
          <div className="no-tickets">
            <p>You haven't purchased any tickets yet.</p>
            <a href="/" className="btn btn-primary">
              Browse Events
            </a>
          </div>
        ) : (
          <div className="tickets-grid">
            {tickets.map((ticket) => (
              <div key={ticket.id} className="ticket-card">
                <div className="ticket-header">
                  <span className={`ticket-status ${getStatusClass(ticket.status)}`}>
                    {ticket.status}
                  </span>
                  <span className="ticket-number">#{ticket.ticket_number}</span>
                </div>

                {ticket.event_image && (
                  <div className="ticket-image">
                    <img src={ticket.event_image} alt={ticket.event_title} />
                  </div>
                )}

                <div className="ticket-body">
                  <h3 className="ticket-event-title">{ticket.event_title}</h3>

                  <div className="ticket-info">
                    <div className="info-row">
                      <span className="info-label">Type:</span>
                      <span className="info-value">{ticket.ticket_type_name}</span>
                    </div>
                    <div className="info-row">
                      <span className="info-label">Venue:</span>
                      <span className="info-value">{ticket.venue}</span>
                    </div>
                    <div className="info-row">
                      <span className="info-label">Date:</span>
                      <span className="info-value">{formatDate(ticket.event_date)}</span>
                    </div>
                    <div className="info-row">
                      <span className="info-label">Price:</span>
                      <span className="info-value">${parseFloat(ticket.price).toFixed(2)}</span>
                    </div>
                  </div>

                  <div className="ticket-qr">
                    {/* Try to render backend PNG if available, else render QR in React */}
                    {ticket.qr_code ? (
                      <img
                        src={`${process.env.REACT_APP_API_URL}/uploads/qrcodes/${ticket.qr_code.replace(/^.*[\\/]/, '')}`}
                        alt={`QR for ${ticket.ticket_number}`}
                        className="qr-image"
                        style={{ width: 120, height: 120 }}
                        onError={e => {
                          e.target.onerror = null;
                          e.target.style.display = 'none';
                          const qrDiv = e.target.parentNode.querySelector('.react-qr');
                          if (qrDiv) qrDiv.style.display = 'block';
                        }}
                      />
                    ) : null}
                    <div className="react-qr" style={{ display: ticket.qr_code ? 'none' : 'block' }}>
                      <QRCodeSVG value={String(ticket.id)} size={120} />
                    </div>
                  </div>

                  <div className="ticket-footer">
                    <small>Order: {ticket.order_number}</small>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default MyTickets;
