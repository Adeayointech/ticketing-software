import React, { useState, useEffect } from 'react';
import { ordersAPI } from '../services/api';
import './MyOrders.css';

const MyOrders = () => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchOrders();
  }, []);

  const fetchOrders = async () => {
    try {
      setLoading(true);
      const response = await ordersAPI.getMyOrders();
      setOrders(response.data.data.orders);
    } catch (err) {
      setError('Failed to load orders');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'completed':
        return 'status-completed';
      case 'pending':
        return 'status-pending';
      case 'failed':
        return 'status-failed';
      case 'refunded':
        return 'status-refunded';
      default:
        return '';
    }
  };

  if (loading) {
    return <div className="container loading">Loading orders...</div>;
  }

  return (
    <div className="my-orders-page">
      <div className="container">
        <h1 className="page-title">My Orders</h1>

        {error && <div className="alert alert-error">{error}</div>}

        {orders.length === 0 ? (
          <div className="no-orders">
            <p>You haven't made any orders yet.</p>
            <a href="/" className="btn btn-primary">
              Browse Events
            </a>
          </div>
        ) : (
          <div className="orders-list">
            {orders.map((order) => (
              <div key={order.id} className="order-card">
                <div className="order-header">
                  <div className="order-info">
                    <h3>Order #{order.order_number}</h3>
                    <p className="order-date">{formatDate(order.created_at)}</p>
                  </div>
                  <div className="order-status-amount">
                    <span className={`order-status ${getStatusColor(order.payment_status)}`}>
                      {order.payment_status}
                    </span>
                    <span className="order-amount">${parseFloat(order.total_amount).toFixed(2)}</span>
                  </div>
                </div>

                <div className="order-body">
                  {order.event_image && (
                    <div className="order-event-image">
                      <img src={order.event_image} alt={order.event_title} />
                    </div>
                  )}
                  <div className="order-details">
                    <h4>{order.event_title}</h4>
                    <div className="order-meta">
                      <div className="meta-item">
                        <span className="meta-icon">📍</span>
                        <span>{order.venue}</span>
                      </div>
                      <div className="meta-item">
                        <span className="meta-icon">📅</span>
                        <span>{formatDate(order.event_date)}</span>
                      </div>
                      <div className="meta-item">
                        <span className="meta-icon">🎟️</span>
                        <span>{order.ticket_count} ticket(s)</span>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="order-footer">
                  <span className="payment-method">
                    Payment: {order.payment_method}
                  </span>
                  {order.payment_status === 'completed' && (
                    <a href={`/my-tickets/${order.id}`} className="btn btn-sm btn-outline">
                      View Tickets
                    </a>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default MyOrders;
