import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import './Navbar.css';

const Navbar = () => {
  const { user, logout, isAuthenticated, isOrganizer } = useAuth();

  return (
    <nav className="navbar">
      <div className="container navbar-container">
        <Link to="/" className="navbar-brand">
          🎟️ Event Ticketing
        </Link>

        <div className="navbar-menu">
          <Link to="/" className="nav-link">
            Events
          </Link>

          {isAuthenticated ? (
            <>
              {isOrganizer ? (
                <>
                  <Link to="/organizer/dashboard" className="nav-link">
                    My Events
                  </Link>
                  <Link to="/organizer/create-event" className="nav-link">
                    Create Event
                  </Link>
                </>
              ) : (
                <>
                  <Link to="/my-tickets" className="nav-link">
                    My Tickets
                  </Link>
                  <Link to="/my-orders" className="nav-link">
                    My Orders
                  </Link>
                </>
              )}
              <Link to="/dashboard" className="nav-link">
                Dashboard
              </Link>
              <div className="navbar-user">
                <span className="user-name">
                  {isOrganizer ? 'organizer' : 'attendee'}
                </span>
                <button onClick={logout} className="btn btn-sm btn-outline">
                  Logout
                </button>
              </div>
            </>
          ) : (
            <>
              <Link to="/login" className="btn btn-sm btn-outline">
                Login
              </Link>
              <Link to="/register" className="btn btn-sm btn-primary">
                Register
              </Link>
            </>
          )}
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
