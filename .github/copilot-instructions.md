# Event Ticketing System - Copilot Instructions

## Project Overview
This is a full-stack event ticketing system designed for cPanel hosting with React frontend and PHP backend.

## Technology Stack
- **Frontend**: React with React Router
- **Backend**: PHP 7.4+ REST API
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Tokens)
- **QR Codes**: PHP QR Code library
- **Deployment**: cPanel compatible

## Project Structure
- `/frontend` - React application
- `/backend` - PHP REST API
- `/database` - SQL schema and migrations

## Development Guidelines
- Use PHP namespaces and PSR-4 autoloading
- Follow REST API conventions
- Use prepared statements for all database queries
- Implement proper error handling and validation
- Use JWT for authentication
- Generate unique QR codes for each ticket
- Ensure mobile-responsive design

## User Roles
- **Attendee**: Browse events, purchase tickets, view tickets
- **Organizer**: Create events, manage ticket types, view sales

## Key Features
- Event creation and management
- Ticket purchasing with multiple types
- QR code generation for tickets
- User authentication and authorization
- Dashboards for attendees and organizers
- Order history and ticket management
