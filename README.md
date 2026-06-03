# AgriChain – Blockchain Enabled Farmer–Consumer Marketplace

## Overview

AgriChain is a blockchain-enabled agricultural e-commerce platform designed to connect farmers directly with consumers while eliminating intermediaries. The platform provides transparent transactions, secure payment mechanisms, immutable order verification, and real-time notifications.

The system improves trust, transparency, and profitability in agricultural trading through blockchain-backed transaction verification and role-based management.

How to Run AgriChain Properly
Step 1: Start XAMPP

Start:

Apache
MySQL
Step 2: Create Database

Open:

http://localhost/phpmyadmin

Create:

agrichain
Step 3: Configure Database

Check:

includes/db.php

Example:

$host = "localhost";
$user = "root";
$password = "";
$dbname = "agrichain";
Step 4: Install PHP Dependencies
composer install
Step 5: Install Node Packages

Project root:

npm install

Blockchain API:

cd blockchain-api
npm install
Step 6: Start Ganache

Open Ganache Desktop.

Create a workspace.

Default RPC:

http://127.0.0.1:7545
Step 7: Deploy Smart Contract

Inside project:

truffle migrate --reset

Expected:

OrderVerifier deployed successfully
Step 8: Start Blockchain API
cd blockchain-api
node index.js

Keep this terminal running.

Step 9: Telegram Bot Setup

Update:

includes/telegram.php

with:

BOT_TOKEN
CHAT_ID
Step 10: Razorpay Setup

Update:

includes/config.php

Add:

RAZORPAY_KEY_ID
RAZORPAY_SECRET
Step 11: Launch

Open:

http://localhost/agrichain
## Key Features

### Farmer Module

* Farmer Registration & Login
* Product Management
* Order Tracking
* Earnings Dashboard
* Wallet Management
* Product Reviews Monitoring
* Farm Profile Management

### Consumer Module

* Product Browsing
* Shopping Cart
* Secure Checkout
* UPI Payment
* Razorpay Integration
* Cash on Delivery
* Order Tracking
* Product Reviews & Ratings

### Admin Module

* User Management
* Farmer Monitoring
* Product Monitoring
* Order Management
* Earnings Monitoring
* Review Management
* Platform Configuration

### Blockchain Features

* Smart Contract Based Verification
* Immutable Order Records
* Ethereum Integration
* Ganache Local Blockchain
* Solidity Smart Contracts
* Web3.js Integration

### Notification System

* Telegram Bot Integration
* Order Notifications
* Payment Updates
* Delivery Status Alerts

---

## Technology Stack

### Frontend

* HTML5
* CSS3
* JavaScript

### Backend

* PHP

### Database

* MySQL

### Blockchain

* Ethereum
* Solidity
* Truffle
* Ganache
* Web3.js

### Payment Integration

* Razorpay
* UPI
* Cash on Delivery

### Notifications

* Telegram Bot API

### Development Tools

* XAMPP
* Visual Studio Code
* Git
* GitHub

---

## Project Structure

<img width="1203" height="645" alt="image" src="https://github.com/user-attachments/assets/cc42c6f3-dcb7-4b0d-9a52-c40a4b0eec24" />


## Installation & Setup

### Clone Repository

git clone https://github.com/iameshureddy/Agrichain.git

### Move Project

Place project inside:

xampp/htdocs/

### Start XAMPP

Start:

* Apache
* MySQL

### Database Setup

1. Open phpMyAdmin
2. Create database:

agrichain

3. Import SQL file

4. Update database credentials:

includes/db.php

### Install Composer Dependencies

composer install

### Install Node Dependencies

npm install

### Blockchain Setup

Start Ganache

Deploy Smart Contracts:

truffle migrate --reset

### Start Blockchain API

cd blockchain-api

npm install

node index.js

### Open Application

http://localhost/agrichain



## User Roles

### Farmer

Manage products, orders, earnings, and farm profile.

### Consumer

Purchase products, track orders, and provide reviews.

### Admin

Monitor platform activities and manage users.

Project Images
<img width="588" height="304" alt="WhatsApp Image 2026-06-04 at 12 35 53 AM" src="https://github.com/user-attachments/assets/1e609df7-cacc-43d0-94cc-51e6a91af1cd" />
<img width="628" height="296" alt="WhatsApp Image 2026-06-04 at 12 36 01 AM" src="https://github.com/user-attachments/assets/2a0dfd76-f518-4408-a97c-4cb223ec5502" />
<img width="659" height="343" alt="WhatsApp Image 2026-06-04 at 12 36 12 AM" src="https://github.com/user-attachments/assets/f4085d0c-0d11-4e68-8874-e39df7db3a79" />
<img width="636" height="294" alt="WhatsApp Image 2026-06-04 at 12 36 21 AM" src="https://github.com/user-attachments/assets/56c7ee1e-8055-4a91-b191-0bbb37d9ffbc" />
<img width="707" height="355" alt="WhatsApp Image 2026-06-04 at 12 36 30 AM" src="https://github.com/user-attachments/assets/6cbd8240-e91b-41bc-af29-1f9163446dcc" />
<img width="662" height="330" alt="WhatsApp Image 2026-06-04 at 12 36 39 AM" src="https://github.com/user-attachments/assets/74eaaf4d-1d27-452e-9c19-381f60a4be37" />
<img width="1366" height="768" alt="image" src="https://github.com/user-attachments/assets/86320cef-1f57-4950-865a-9593f5888efb" />


## Future Enhancements

* Mobile Application
* AI-Based Crop Recommendations
* Product Recommendation Engine
* Cloud Deployment
* QR-Based Product Verification
* Multi-Language Support
* Advanced Analytics Dashboard


