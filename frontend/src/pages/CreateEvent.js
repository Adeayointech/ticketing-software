import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { eventsAPI } from '../services/api';
import './CreateEvent.css';

const CreateEvent = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    venue: '',
    address: '',
    event_date: '',
    end_date: '',
    image_url: '',
    status: 'published',
  });
  const [ticketTypes, setTicketTypes] = useState([
    { name: 'General Admission', description: '', price: '', quantity: '' },
  ]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleTicketTypeChange = (index, field, value) => {
    const newTicketTypes = [...ticketTypes];
    newTicketTypes[index][field] = value;
    setTicketTypes(newTicketTypes);
  };

  const addTicketType = () => {
    setTicketTypes([
      ...ticketTypes,
      { name: '', description: '', price: '', quantity: '' },
    ]);
  };

  const removeTicketType = (index) => {
    if (ticketTypes.length > 1) {
      const newTicketTypes = ticketTypes.filter((_, i) => i !== index);
      setTicketTypes(newTicketTypes);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const eventData = {
        ...formData,
        ticket_types: ticketTypes.filter((tt) => tt.name && tt.price && tt.quantity),
      };

      const response = await eventsAPI.create(eventData);
      navigate('/organizer/dashboard');
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to create event');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="create-event-page">
      <div className="container">
        <div className="page-header">
          <h1>Create New Event</h1>
          <p>Fill in the details to create your event</p>
        </div>

        {error && <div className="alert alert-error">{error}</div>}

        <form onSubmit={handleSubmit} className="create-event-form">
          <div className="form-section">
            <h2>Event Information</h2>

            <div className="form-group">
              <label>Event Title *</label>
              <input
                type="text"
                name="title"
                className="form-control"
                value={formData.title}
                onChange={handleChange}
                required
                placeholder="Enter event title"
              />
            </div>

            <div className="form-group">
              <label>Description</label>
              <textarea
                name="description"
                className="form-control"
                value={formData.description}
                onChange={handleChange}
                rows="5"
                placeholder="Tell people about your event"
              />
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Venue *</label>
                <input
                  type="text"
                  name="venue"
                  className="form-control"
                  value={formData.venue}
                  onChange={handleChange}
                  required
                  placeholder="Venue name"
                />
              </div>

              <div className="form-group">
                <label>Address</label>
                <input
                  type="text"
                  name="address"
                  className="form-control"
                  value={formData.address}
                  onChange={handleChange}
                  placeholder="Full address"
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label>Start Date & Time *</label>
                <input
                  type="datetime-local"
                  name="event_date"
                  className="form-control"
                  value={formData.event_date}
                  onChange={handleChange}
                  required
                />
              </div>

              <div className="form-group">
                <label>End Date & Time</label>
                <input
                  type="datetime-local"
                  name="end_date"
                  className="form-control"
                  value={formData.end_date}
                  onChange={handleChange}
                />
              </div>
            </div>

            <div className="form-group">
              <label>Image URL</label>
              <input
                type="url"
                name="image_url"
                className="form-control"
                value={formData.image_url}
                onChange={handleChange}
                placeholder="https://example.com/image.jpg"
              />
              <small className="form-text">Enter a URL to an event image</small>
            </div>

            <div className="form-group">
              <label>Status</label>
              <select
                name="status"
                className="form-control"
                value={formData.status}
                onChange={handleChange}
              >
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
            </div>
          </div>

          <div className="form-section">
            <div className="section-header">
              <h2>Ticket Types</h2>
              <button
                type="button"
                onClick={addTicketType}
                className="btn btn-sm btn-outline"
              >
                + Add Ticket Type
              </button>
            </div>

            {ticketTypes.map((ticketType, index) => (
              <div key={index} className="ticket-type-form">
                <div className="ticket-type-header">
                  <h3>Ticket Type #{index + 1}</h3>
                  {ticketTypes.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeTicketType(index)}
                      className="btn-remove"
                    >
                      Remove
                    </button>
                  )}
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label>Ticket Name *</label>
                    <input
                      type="text"
                      className="form-control"
                      value={ticketType.name}
                      onChange={(e) =>
                        handleTicketTypeChange(index, 'name', e.target.value)
                      }
                      required
                      placeholder="e.g., General Admission, VIP"
                    />
                  </div>

                  <div className="form-group">
                    <label>Price *</label>
                    <input
                      type="number"
                      step="0.01"
                      className="form-control"
                      value={ticketType.price}
                      onChange={(e) =>
                        handleTicketTypeChange(index, 'price', e.target.value)
                      }
                      required
                      placeholder="0.00"
                    />
                  </div>

                  <div className="form-group">
                    <label>Quantity *</label>
                    <input
                      type="number"
                      className="form-control"
                      value={ticketType.quantity}
                      onChange={(e) =>
                        handleTicketTypeChange(index, 'quantity', e.target.value)
                      }
                      required
                      placeholder="100"
                    />
                  </div>
                </div>

                <div className="form-group">
                  <label>Description</label>
                  <input
                    type="text"
                    className="form-control"
                    value={ticketType.description}
                    onChange={(e) =>
                      handleTicketTypeChange(index, 'description', e.target.value)
                    }
                    placeholder="Optional description"
                  />
                </div>
              </div>
            ))}
          </div>

          <div className="form-actions">
            <button
              type="button"
              onClick={() => navigate('/organizer/dashboard')}
              className="btn btn-secondary"
            >
              Cancel
            </button>
            <button type="submit" className="btn btn-success" disabled={loading}>
              {loading ? 'Creating Event...' : 'Create Event'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CreateEvent;
