import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { eventsAPI } from '../services/api';
import EventCard from '../components/EventCard';
import './Home.css';

const Home = () => {
  const [events, setEvents] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [error, setError] = useState('');

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      setLoading(true);
      const response = await eventsAPI.getAll({ status: 'published', upcoming: true });
      setEvents(response.data.data.events);
      setError('');
    } catch (err) {
      setError('Failed to load events');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = async (e) => {
    e.preventDefault();
    try {
      setLoading(true);
      const response = await eventsAPI.getAll({
        status: 'published',
        upcoming: true,
        search: searchTerm,
      });
      setEvents(response.data.data.events);
    } catch (err) {
      setError('Search failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="home-page">
      <div className="hero-section">
        <div className="hero-background">
          <div className="floating-shapes">
            <div className="shape shape-1"></div>
            <div className="shape shape-2"></div>
            <div className="shape shape-3"></div>
          </div>
        </div>
        <div className="container hero-content">
          <div className="hero-badge">🎉 Your Gateway to Amazing Events</div>
          <h1 className="hero-title">Experience Live Events<br />Like Never Before</h1>
          <p className="hero-subtitle">
            Discover concerts, conferences, sports events, and more.<br />Book your tickets instantly and enjoy seamless entry with QR codes.
          </p>
          <form onSubmit={handleSearch} className="search-form">
            <input
              type="text"
              placeholder="Search for events, venues, or artists..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="search-input"
            />
            <button type="submit" className="btn btn-search">
              🔍 Search
            </button>
          </form>
          <div className="hero-stats">
            <div className="stat-item">
              <div className="stat-number">10K+</div>
              <div className="stat-label">Events Hosted</div>
            </div>
            <div className="stat-item">
              <div className="stat-number">500K+</div>
              <div className="stat-label">Happy Attendees</div>
            </div>
            <div className="stat-item">
              <div className="stat-number">1K+</div>
              <div className="stat-label">Organizers</div>
            </div>
          </div>
        </div>
      </div>

      <div className="container">
        <div className="events-section">
          <div className="section-header">
            <h2 className="section-title">🔥 Trending Events</h2>
            <p className="section-subtitle">Don't miss out on the hottest events</p>
          </div>

          {error && <div className="alert alert-error">{error}</div>}

          {loading ? (
            <div className="loading">Loading events...</div>
          ) : events.length === 0 ? (
            <div className="no-events">
              <p>No events found. Check back later!</p>
            </div>
          ) : (
            <div className="events-grid">
              {events.map((event) => (
                <EventCard key={event.id} event={event} />
              ))}
            </div>
          )}
        </div>
      </div>

      <footer className="footer">
        <div className="container">
          <div className="footer-content">
            <div className="footer-section">
              <h3 className="footer-title">
                <span className="footer-logo">A</span> Accesio
              </h3>
              <p className="footer-description">
                Your trusted platform for discovering and booking amazing events. Experience seamless ticketing with QR code technology.
              </p>
            </div>
            <div className="footer-section">
              <h4 className="footer-heading">Quick Links</h4>
              <ul className="footer-links">
                <li><Link to="/">Browse Events</Link></li>
                <li><Link to="/register">Create Account</Link></li>
                <li><Link to="/login">Sign In</Link></li>
              </ul>
            </div>
            <div className="footer-section">
              <h4 className="footer-heading">For Organizers</h4>
              <ul className="footer-links">
                <li><Link to="/register">Host an Event</Link></li>
                <li><Link to="/organizer/dashboard">Organizer Dashboard</Link></li>
                <li><a href="#pricing">Pricing</a></li>
              </ul>
            </div>
            <div className="footer-section">
              <h4 className="footer-heading">Connect With Us</h4>
              <div className="social-links">
                <a href="#facebook" className="social-link">Facebook</a>
                <a href="#twitter" className="social-link">Twitter</a>
                <a href="#instagram" className="social-link">Instagram</a>
                <a href="#linkedin" className="social-link">LinkedIn</a>
              </div>
            </div>
          </div>
          <div className="footer-bottom">
            <p>&copy; 2026 Accesio. All rights reserved.</p>
            <div className="footer-bottom-links">
              <a href="#privacy">Privacy Policy</a>
              <a href="#terms">Terms of Service</a>
              <a href="#contact">Contact Us</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Home;
