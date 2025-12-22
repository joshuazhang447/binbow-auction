# Binbow Online Auction


**[🚀 Live Demo](https://zzhang.scweb.ca/auction)**


## 📖 About The Project

Binbow Online Auction is a fully dynamic, PHP-based auction platform that allows users to list items for sale, bid on active auctions, and process transactions upon completion.

This project focuses on backend integrity, secure data processing, and code reliability. It moves beyond simple CRUD operations by implementing a logic-heavy auction lifecycle, integrating external APIs, and ensuring stability through rigorous testing.

## ✨ Key Features

This application is feature-complete and robust, utilizing modern backend practices:

*   **Real PayPal API Integration:**
    *   Fully functional payment gateway implementation.
    *   Automatically generates dynamic PayPal checkout links for winning bidders based on the final bid amount.
    *   Handles transaction verification in a Sandbox environment.
*   **Secure Authentication System:**
    *   Complete Registration and Login functionality.
    *   **Security First:** Utilizes advanced hashing algorithms for storing passwords and sensitive user data. No plain text passwords are stored in the database.
*   **Comprehensive Unit Testing:**
    *   **Fully Tested Codebase:** The application features a robust suite of unit tests ensuring stability across all major features.
    *   **Test Coverage:** Includes specific tests for **Bidding Logic, Database Connections, Payment Processing, User Sessions, Mail Systems, and Category Management**.
    *   Ensures that critical business logic (like determining a winner or handling currency) functions correctly without regression.
*   **Robust Database Architecture (MariaDB):**
    *   Fully relational database design connecting Users, Items, Bids, and Categories.
    *   Dynamic content rendering—nothing is hardcoded; all items and bids are fetched in real-time.
*   **Auction Lifecycle Logic:**
    *   **Bidding System:** Real-time validation to ensure new bids are higher than current bids.
    *   **Process Auction:** A dedicated logic controller that detects expired auctions, locks them from future bidding, identifies the highest bidder, and generates the "Pay Now" interface.
*   **Item Management:**
    *   User-friendly interface for adding new items with image uploads, descriptions, categories, and setting expiration dates.

## 📸 Application Screenshots

### 1. Home / Dashboard
The main landing page displaying active auctions, current bids, and item prices dynamically fetched from the database.
![Home Page](https://i.imgur.com/kPs9Cr6.png)

### 2. Item Details & Bidding
A detailed view of a specific lot. Users can view bid history and place new bids. The system validates the input to ensure it meets the increment requirements.
![Item Page](https://i.imgur.com/LUBG4sS.png)

### 3. Registration & Security
A secure registration form. All input data is sanitized, and credentials are hashed before insertion into the MariaDB database to ensure user privacy.
![Register Page](https://i.imgur.com/Kviaq5q.png)

### 4. Auction Processing & Payment
The "Process Auction" view. Once an auction expires, this feature calculates the winner and integrates with the PayPal API to generate a secure payment link for the winning bidder.
![Process Auction](https://i.imgur.com/jWA2kZS.png)

## 🛠️ Built With

*   **Language:** PHP (Server-side logic)
*   **Database:** MariaDB / MySQL
*   **Testing:** PHPUnit (Unit Testing)
*   **API:** PayPal REST API
*   **Frontend:** HTML5, CSS3, Bootstrap
*   **Tools:** phpMyAdmin

## 🚀 How It Works

1.  **Register:** Create an account to start participating.
2.  **Browse & Bid:** Navigate through categories or view all items. Place a bid on an active item.
3.  **Monitor:** Watch the "Bid History" to see if you are outbid.
4.  **Process:** When an auction reaches its end date, the system locks the item.
5.  **Win & Pay:** The winner receives a notification with a generated **PayPal** button to complete the purchase securely.

## 👤 Author

**Joshua Z**
