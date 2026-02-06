import React from 'react';
import { Link } from 'react-router-dom';
import './EventCard.css';

const EventCard = ({ event }) => {
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

  const getMinPrice = () => {
    if (!event.ticket_types || event.ticket_types.length === 0) return 'Free';
    const minPrice = Math.min(...event.ticket_types.map((t) => parseFloat(t.price)));
    return minPrice === 0 ? 'Free' : `$${minPrice.toFixed(2)}`;
  };

  return (
    <div className="event-card">
      {event.image_url && (
        <div className="event-card-image">
          <img src={event.image_url} alt={event.title} />
        </div>
      )}
      <div className="event-card-content">
        <h3 className="event-card-title">{event.title}</h3>
        <div className="event-card-info">
          <div className="event-info-item">
            <span className="info-icon">📅</span>
            <span>{formatDate(event.event_date)}</span>
          </div>
          <div className="event-info-item">
            <span className="info-icon">📍</span>
            <span>{event.venue}</span>
          </div>
          <div className="event-info-item">
            <span className="info-icon">💰</span>
            <span>From {getMinPrice()}</span>
          </div>
        </div>
        {event.description && (
          <p className="event-card-description">
            {event.description.substring(0, 120)}
            {event.description.length > 120 ? '...' : ''}
          </p>
        )}
        <div className="event-card-footer">
          <span className="tickets-sold">{event.tickets_sold || 0} tickets sold</span>
          <Link to={`/events/${event.id}`} className="btn btn-primary">
            View Details
          </Link>
        </div>
      </div>
    </div>
  );
};

export default EventCard;
