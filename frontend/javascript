// server.js
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Middleware
app.use(bodyParser.json());
app.use(cors());

// Route to handle the addresses
app.post('/update-location', (req, res) => {
    const { patientAddress, hospitalLocation } = req.body;

    console.log(`Patient Address: ${patientAddress}`);
    console.log(`Hospital Location: ${hospitalLocation}`);

    // Perform operations like calculating distance, price, or finding nearby hospitals
    const estimatedPrice = Math.floor(Math.random() * 1000) + 500; // Mock price calculation

    res.json({
        message: "Location data received!",
        patientAddress,
        hospitalLocation,
        estimatedPrice: `₹${estimatedPrice}`
    });
});

// Start the server
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
