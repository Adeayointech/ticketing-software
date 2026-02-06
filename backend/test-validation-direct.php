<!DOCTYPE html>
<html>
<head>
    <title>Direct Validation Test</title>
</head>
<body>
    <h2>Testing Validation Endpoint Directly</h2>
    
    <h3>Debug: Check URL Parsing</h3>
    <p><a href="http://localhost/ticketing-backend/tickets/validate?debug=1" target="_blank">Test URL Parsing for /tickets/validate</a></p>
    <p><a href="http://localhost/ticketing-backend/tickets?debug=1" target="_blank">Test URL Parsing for /tickets</a></p>
    <p><a href="http://localhost/ticketing-backend?debug=1" target="_blank">Test URL Parsing for root</a></p>
    <hr>
    
    <?php
    // Get a ticket number from the database for testing
    require_once __DIR__ . '/config/Database.php';
    $db = new Config\Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT ticket_number, status FROM tickets WHERE status = 'valid' LIMIT 1");
    $stmt->execute();
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ticket) {
        echo "<p>Found test ticket: <strong>" . htmlspecialchars($ticket['ticket_number']) . "</strong></p>";
        echo "<p>Status: " . htmlspecialchars($ticket['status']) . "</p>";
        
        // Now test the endpoint
        $url = 'http://localhost/ticketing-backend/tickets/validate';
        $data = json_encode(['ticket_number' => $ticket['ticket_number']]);
        
        // Get JWT token from localStorage (you'll need to paste this manually)
        ?>
        
        <h3>Test Form</h3>
        <form id="testForm">
            <label>JWT Token:</label><br>
            <input type="text" id="token" size="100" placeholder="Paste your JWT token here"><br><br>
            <button type="submit">Test Validation</button>
        </form>
        
        <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
        
        <script>
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const token = document.getElementById('token').value;
            const resultDiv = document.getElementById('result');
            
            try {
                resultDiv.innerHTML = '<p>Testing validation...</p>';
                
                const response = await fetch('<?php echo $url; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: '<?php echo $data; ?>'
                });
                
                const result = await response.json();
                
                resultDiv.innerHTML = `
                    <h4>Response (Status: ${response.status})</h4>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                `;
            } catch (error) {
                resultDiv.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
            }
        });
        </script>
        
        <?php
    } else {
        echo "<p style='color: red;'>No valid tickets found in database!</p>";
    }
    ?>
    
    <hr>
    <h3>Check Apache Error Log</h3>
    <p>After testing, check <code>C:\xampp\apache\logs\error.log</code> for the DEBUG line showing Resource, Action, Method, and Segments.</p>
</body>
</html>
