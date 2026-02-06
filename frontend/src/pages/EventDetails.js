import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { eventsAPI, ticketsAPI } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import './EventDetails.css';

const EventDetails = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  const [event, setEvent] = useState(null);
  const [loading, setLoading] = useState(true);
  const [purchasing, setPurchasing] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [selectedTickets, setSelectedTickets] = useState({});

  useEffect(() => {
    fetchEvent();
  }, [id]);

  const fetchEvent = async () => {
    try {
      setLoading(true);
      const response = await eventsAPI.getById(id);
      setEvent(response.data.data.event);
    } catch (err) {
      setError('Failed to load event details');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const handleQuantityChange = (ticketTypeId, quantity) => {
    setSelectedTickets({
      ...selectedTickets,
      [ticketTypeId]: parseInt(quantity) || 0,
    });
  };

  const getTotalAmount = () => {
    let total = 0;
    Object.keys(selectedTickets).forEach((ticketTypeId) => {
      const quantity = selectedTickets[ticketTypeId];
      const ticketType = event.ticket_types.find((t) => t.id == ticketTypeId);
      if (ticketType && quantity > 0) {
        total += ticketType.price * quantity;
      }
    });
    return total;
  };

  const handlePurchase = async () => {
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    const tickets = [];
    Object.keys(selectedTickets).forEach((ticketTypeId) => {
      const quantity = selectedTickets[ticketTypeId];
      if (quantity > 0) {
        tickets.push({
          ticket_type_id: parseInt(ticketTypeId),
          quantity: quantity,
        });
      }
    });

    if (tickets.length === 0) {
      setError('Please select at least one ticket');
      return;
    }

    setPurchasing(true);
    setError('');

    try {
      const response = await ticketsAPI.purchase({
        event_id: id,
        tickets: tickets,
        payment_method: 'simulated',
      });

      setSuccess('Tickets purchased successfully!');
      setTimeout(() => {
        navigate('/my-tickets');
      }, 2000);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to purchase tickets');
    } finally {
      setPurchasing(false);
    }
  };

  if (loading) {
    return <div className="container loading">Loading event...</div>;
  }

  if (!event) {
    return (
      <div className="container">
        <div className="alert alert-error">Event not found</div>
      </div>
    );
  }

  return (
    <div className="event-details-page">
      <div className="container">
        {event.image_url && (
          <div className="event-banner">
            <img src={event.image_url} alt={event.title} />
          </div>
        )}

        <div className="event-details-content">
          <div className="event-main-info">
            <h1 className="event-title">{event.title}</h1>

            <div className="event-meta">
              <div className="meta-item">
                <span className="meta-icon">📅</span>
                <div>
                  <strong>Date & Time</strong>
                  <p>{formatDate(event.event_date)}</p>
                </div>
              </div>

              <div className="meta-item">
                <span className="meta-icon">📍</span>
                <div>
                  <strong>Venue</strong>
                  <p>{event.venue}</p>
                  {event.address && <p className="meta-address">{event.address}</p>}
                </div>
              </div>

              <div className="meta-item">
                <span className="meta-icon">👤</span>
                <div>
                  <strong>Organizer</strong>
                  <p>
                    {event.first_name} {event.last_name}
                  </p>
                </div>
              </div>
            </div>

            {event.description && (
              <div className="event-description">
                <h2>About This Event</h2>
                <p>{event.description}</p>
              </div>
            )}
          </div>

          <div className="ticket-purchase-card">
            <h2>Select Tickets</h2>

            {error && <div className="alert alert-error">{error}</div>}
            {success && <div className="alert alert-success">{success}</div>}

            {event.ticket_types && event.ticket_types.length > 0 ? (
              <>
                {/* Only show purchase UI for attendees */}
                {user?.role === 'attendee' && (
                  <>
                    <div className="ticket-types">
                      {event.ticket_types.map((ticketType) => {
                        const available = ticketType.quantity - ticketType.quantity_sold;
                        return (
                          <div key={ticketType.id} className="ticket-type">
                            <div className="ticket-type-info">
                              <h3>{ticketType.name}</h3>
                              {ticketType.description && <p>{ticketType.description}</p>}
                              <div className="ticket-price">${parseFloat(ticketType.price).toFixed(2)}</div>
                              <div className="ticket-availability">
                                {available > 0 ? `${available} available` : 'Sold Out'}
                              </div>
                            </div>
                            <div className="ticket-quantity">
                              <label>Quantity:</label>
                              <input
                                type="number"
                                min="0"
                                max={available}
                                value={selectedTickets[ticketType.id] || 0}
                                onChange={(e) => handleQuantityChange(ticketType.id, e.target.value)}
                                disabled={available === 0}
                                className="quantity-input"
                              />
                            </div>
                          </div>
                        );
                      })}
                    </div>
                    <div className="purchase-summary">
                      <div className="total-amount">
                        <span>Total:</span>
                        <span className="amount">${getTotalAmount().toFixed(2)}</span>
                      </div>
                      <button
                        onClick={handlePurchase}
                        disabled={purchasing || getTotalAmount() === 0}
                        className="btn btn-success btn-block"
                      >
                        {purchasing ? 'Processing...' : 'Purchase Tickets'}
                      </button>
                      {!isAuthenticated && (
                        <p className="login-notice">You need to login to purchase tickets</p>
                      )}
                    </div>
                  </>
                )}
              </>
            ) : (
              <p>No tickets available for this event</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default EventDetails;
