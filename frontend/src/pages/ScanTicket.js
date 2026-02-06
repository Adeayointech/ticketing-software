import React, { useState } from 'react';
import { ticketsAPI } from '../services/api';

const ScanTicket = () => {
  // const { user } = useAuth(); // Not needed here
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
      // Debug: log which API is being called and payload
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
      <h1>Scan Ticket</h1>
      <div>
        <input
          type="text"
          placeholder="Enter or scan ticket number"
          value={ticketNumber}
          onChange={e => setTicketNumber(e.target.value)}
        />
        <button onClick={handleScan} disabled={loading}>
          {loading ? 'Validating...' : 'Validate Ticket'}
        </button>
      </div>
      {error && <div className="alert alert-error">{error}</div>}
      {result && (
        <div className="alert alert-success">
          {result.message || 'Ticket is valid!'}
        </div>
      )}
    </div>
  );
};

export default ScanTicket;
