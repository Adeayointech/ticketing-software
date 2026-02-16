import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import api from '../services/api';
import './Profile.css';

function Profile() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [profile, setProfile] = useState({
    name: '',
    email: '',
    phone: '',
    current_password: '',
    new_password: '',
    confirm_password: ''
  });
  const [isEditing, setIsEditing] = useState(false);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (user) {
      setProfile({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        current_password: '',
        new_password: '',
        confirm_password: ''
      });
    }
  }, [user]);

  const handleChange = (e) => {
    setProfile({
      ...profile,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage({ type: '', text: '' });

    // Validate password if changing
    if (profile.new_password) {
      if (profile.new_password !== profile.confirm_password) {
        setMessage({ type: 'error', text: 'New passwords do not match!' });
        setLoading(false);
        return;
      }
      if (!profile.current_password) {
        setMessage({ type: 'error', text: 'Current password is required to change password!' });
        setLoading(false);
        return;
      }
    }

    try {
      const updateData = {
        name: profile.name,
        email: profile.email,
        phone: profile.phone
      };

      if (profile.new_password) {
        updateData.current_password = profile.current_password;
        updateData.new_password = profile.new_password;
      }

      const response = await api.put('/users/profile', updateData);
      
      setMessage({ type: 'success', text: 'Profile updated successfully!' });
      setIsEditing(false);
      
      // Clear password fields
      setProfile({
        ...profile,
        current_password: '',
        new_password: '',
        confirm_password: ''
      });

      // Update user context if name or email changed
      if (response.data.user) {
        // You might want to update the AuthContext here
      }
    } catch (error) {
      setMessage({ 
        type: 'error', 
        text: error.response?.data?.message || 'Failed to update profile' 
      });
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="profile-container">
      <div className="profile-wrapper">
        <div className="profile-header">
          <div className="profile-avatar-large">
            {user?.name?.charAt(0).toUpperCase() || 'U'}
          </div>
          <h1 className="profile-title">My Profile</h1>
          <p className="profile-subtitle">Manage your account information</p>
        </div>

        {message.text && (
          <div className={`alert alert-${message.type}`}>
            {message.text}
          </div>
        )}

        <form onSubmit={handleSubmit} className="profile-form">
          <div className="form-section">
            <h3 className="section-title">Personal Information</h3>
            
            <div className="form-group">
              <label>Full Name</label>
              <input
                type="text"
                name="name"
                value={profile.name}
                onChange={handleChange}
                disabled={!isEditing}
                required
              />
            </div>

            <div className="form-group">
              <label>Email Address</label>
              <input
                type="email"
                name="email"
                value={profile.email}
                onChange={handleChange}
                disabled={!isEditing}
                required
              />
            </div>

            <div className="form-group">
              <label>Phone Number</label>
              <input
                type="tel"
                name="phone"
                value={profile.phone}
                onChange={handleChange}
                disabled={!isEditing}
                placeholder="Enter phone number"
              />
            </div>

            <div className="form-group">
              <label>User Role</label>
              <input
                type="text"
                value={user?.role || 'N/A'}
                disabled
                className="role-input"
              />
            </div>
          </div>

          {isEditing && (
            <div className="form-section">
              <h3 className="section-title">Change Password (Optional)</h3>
              
              <div className="form-group">
                <label>Current Password</label>
                <input
                  type="password"
                  name="current_password"
                  value={profile.current_password}
                  onChange={handleChange}
                  placeholder="Enter current password"
                />
              </div>

              <div className="form-group">
                <label>New Password</label>
                <input
                  type="password"
                  name="new_password"
                  value={profile.new_password}
                  onChange={handleChange}
                  placeholder="Enter new password"
                />
              </div>

              <div className="form-group">
                <label>Confirm New Password</label>
                <input
                  type="password"
                  name="confirm_password"
                  value={profile.confirm_password}
                  onChange={handleChange}
                  placeholder="Confirm new password"
                />
              </div>
            </div>
          )}

          <div className="profile-actions">
            {!isEditing ? (
              <>
                <button 
                  type="button" 
                  onClick={() => setIsEditing(true)} 
                  className="btn-primary"
                >
                  ✏️ Edit Profile
                </button>
                <button 
                  type="button" 
                  onClick={handleLogout} 
                  className="btn-logout"
                >
                  🚪 Logout
                </button>
              </>
            ) : (
              <>
                <button 
                  type="submit" 
                  className="btn-primary" 
                  disabled={loading}
                >
                  {loading ? '⏳ Saving...' : '💾 Save Changes'}
                </button>
                <button 
                  type="button" 
                  onClick={() => {
                    setIsEditing(false);
                    setProfile({
                      ...profile,
                      current_password: '',
                      new_password: '',
                      confirm_password: ''
                    });
                    setMessage({ type: '', text: '' });
                  }} 
                  className="btn-secondary"
                  disabled={loading}
                >
                  ❌ Cancel
                </button>
              </>
            )}
          </div>
        </form>

        <div className="profile-footer">
          <button onClick={() => navigate(-1)} className="btn-back">
            ← Back to Dashboard
          </button>
        </div>
      </div>
    </div>
  );
}

export default Profile;
