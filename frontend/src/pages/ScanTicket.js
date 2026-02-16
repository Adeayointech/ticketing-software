import React, { useState } from 'react';
import { ticketsAPI } from '../services/api';
import './ScanTicket.css';

const ScanTicket = () => {
  const [ticketNumber, setTicketNumber] = useState('');
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleScan = async () => {
    setError('');
    setResult(null);
    if (!ticketNumber) {
      setError('Please enter or scan a ticket number');
      return;
    }
    setLoading(true);
    try {
      console.log('Calling ticketsAPI.validateTicket with:', ticketNumber);
      const response = await ticketsAPI.validateTicket(ticketNumber);
      setResult(response.data);
    } catch (err) {
      setError(err.response?.data?.message || 'Invalid or already used ticket');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="scan-ticket-page">
      <div className="container">
        <div className="scan-ticket-container">
          <div className="scan-header">
            <div className="scan-icon">📷</div>
            <h1 className="scan-title">Scan Ticket</h1>
            <p className="scan-subtitle">Enter or scan the ticket number to validate entry</p>
          </div>
          
          <div className="scan-form">
            <input
              type="text"
              className="scan-input"
              placeholder="Enter or scan ticket number"
              value={ticketNumber}
              onChange={e => setTicketNumber(e.target.value)}
              onKeyPress={e => e.key === 'Enter' && handleScan()}
            />
            <button 
              className="btn-validate" 
              onClick={handleScan} 
              disabled={loading}
            >
              {loading ? 'Validating...' : '✓ Validate Ticket'}
            </button>
          </div>

          {error && (
            <div className="result-card error-card">
              <div className="result-icon">❌</div>
              <div className="result-message">{error}</div>
            </div>
          )}
          
          {result && (
            <div className="result-card success-card">
              <div className="result-icon">✓</div>
              <div className="result-message">{result.message || 'Ticket is valid!'}</div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ScanTicket;
