import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { eventsAPI } from '../services/api';
import './CreateEvent.css';

const EditEvent = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
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
  const [ticketTypes, setTicketTypes] = useState([]);
  const [deletedTicketTypes, setDeletedTicketTypes] = useState([]);

  useEffect(() => {
    fetchEventData();
  }, [id]);

  const fetchEventData = async () => {
    try {
      setLoading(true);
      const response = await eventsAPI.getById(id);
      const event = response.data.data.event;
      
      // Format dates for datetime-local input
      const formatDate = (dateStr) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toISOString().slice(0, 16);
      };

      setFormData({
        title: event.title || '',
        description: event.description || '',
        venue: event.venue || '',
        address: event.address || '',
        event_date: formatDate(event.event_date),
        end_date: formatDate(event.end_date),
        image_url: event.image_url || '',
        status: event.status || 'published',
      });

      setTicketTypes(event.ticket_types || []);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to load event');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const toggleStatus = () => {
    setFormData({
      ...formData,
      status: formData.status === 'published' ? 'draft' : 'published'
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
      { name: '', description: '', price: '', quantity: '', is_new: true },
    ]);
  };

  const removeTicketType = (index) => {
    const ticketType = ticketTypes[index];
    
    // If ticket has an ID (existing ticket), add to deleted list
    if (ticketType.id) {
      setDeletedTicketTypes([...deletedTicketTypes, ticketType.id]);
    }
    
    // Remove from list
    const newTicketTypes = ticketTypes.filter((_, i) => i !== index);
    setTicketTypes(newTicketTypes);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setSaving(true);

    try {
      const eventData = {
        ...formData,
        ticket_types: ticketTypes.filter((tt) => tt.name && tt.price && tt.quantity),
        deleted_ticket_types: deletedTicketTypes
      };

      await eventsAPI.update(id, eventData);
      setSuccess('Event updated successfully!');
      
      setTimeout(() => {
        navigate('/organizer/dashboard');
      }, 1500);
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to update event');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="create-event-page">
        <div className="container">
          <div className="loading-state">
            <div className="spinner"></div>
            <p>Loading event details...</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="create-event-page">
      <div className="container">
        <div className="page-header">
          <h1>Edit Event</h1>
          <p>Update your event details</p>
        </div>

        {error && <div className="alert alert-error">{error}</div>}
        {success && <div className="alert alert-success">{success}</div>}

        <form onSubmit={handleSubmit} className="create-event-form">
          <div className="form-section">
            <div className="section-header">
              <h2>Event Information</h2>
              <button
                type="button"
                onClick={toggleStatus}
                className={`btn btn-sm ${formData.status === 'published' ? 'btn-success' : 'btn-warning'}`}
              >
                {formData.status === 'published' ? '✓ Published' : '📝 Draft'} - Click to {formData.status === 'published' ? 'Unpublish' : 'Publish'}
              </button>
            </div>

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

            {ticketTypes.length === 0 && (
              <div className="empty-state">
                <p>No ticket types yet. Click "Add Ticket Type" to create one.</p>
              </div>
            )}

            {ticketTypes.map((ticketType, index) => (
              <div key={index} className="ticket-type-form">
                <div className="ticket-type-header">
                  <h3>
                    {ticketType.name || `Ticket Type #${index + 1}`}
                    {ticketType.tickets_sold > 0 && (
                      <span className="sold-badge">
                        {ticketType.tickets_sold} sold
                      </span>
                    )}
                  </h3>
                  <button
                    type="button"
                    onClick={() => removeTicketType(index)}
                    className="btn-remove"
                    disabled={ticketType.tickets_sold > 0}
                    title={ticketType.tickets_sold > 0 ? 'Cannot delete ticket type with sold tickets' : 'Delete ticket type'}
                  >
                    {ticketType.tickets_sold > 0 ? '🔒 Has Sales' : '🗑️ Delete'}
                  </button>
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
                      min={ticketType.tickets_sold || 0}
                    />
                    {ticketType.tickets_sold > 0 && (
                      <small className="form-text">
                        Minimum: {ticketType.tickets_sold} (already sold)
                      </small>
                    )}
                  </div>
                </div>

                <div className="form-group">
                  <label>Description</label>
                  <textarea
                    className="form-control"
                    value={ticketType.description || ''}
                    onChange={(e) =>
                      handleTicketTypeChange(index, 'description', e.target.value)
                    }
                    rows="2"
                    placeholder="Describe what's included with this ticket"
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
              disabled={saving}
            >
              Cancel
            </button>
            <button type="submit" className="btn btn-primary" disabled={saving}>
              {saving ? '💾 Saving...' : '💾 Save Changes'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EditEvent;
