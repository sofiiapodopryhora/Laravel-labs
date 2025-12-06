<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TaskFlow+ Live</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #log {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            background-color: #fafafa;
            min-height: 200px;
            max-height: 400px;
            overflow-y: auto;
        }
        .log-entry {
            margin: 5px 0;
            padding: 8px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
            background-color: white;
        }
        .log-entry.task-update {
            border-left-color: #28a745;
        }
        .log-entry.comment {
            border-left-color: #17a2b8;
        }
        .log-entry.connection {
            border-left-color: #ffc107;
        }
        .log-entry.error {
            border-left-color: #dc3545;
        }
        .controls {
            margin: 20px 0;
        }
        .controls button {
            margin: 0 10px 10px 0;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-info { background-color: #17a2b8; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .status {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .status.connected { background-color: #d4edda; color: #155724; }
        .status.disconnected { background-color: #f8d7da; color: #721c24; }
        .status.connecting { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 TaskFlow+ Live Events</h1>
        <p>Real-time updates for tasks and comments</p>
        
        <div id="connection-status" class="status connecting">
            📡 Connecting to WebSocket...
        </div>

        <div class="controls">
            <button id="clear-log" class="btn-warning">🗑️ Clear Log</button>
            <button id="test-connection" class="btn-info">🔄 Test Connection</button>
            <button id="create-test-task" class="btn-primary">➕ Create Test Task</button>
            <button id="create-test-comment" class="btn-success">💬 Create Test Comment</button>
        </div>

        <div id="log">
            <div class="log-entry connection">
                📋 Waiting for real-time events...
            </div>
        </div>
    </div>

    <script>
        const projectId = 1; // Test project ID
        const log = document.getElementById('log');
        const statusEl = document.getElementById('connection-status');
        
        // Helper function to add log entries
        const addLog = (message, type = 'info') => {
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.innerHTML = `<small>${timestamp}</small><br>${message}`;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        };

        // Connection status management
        const updateConnectionStatus = (status, message) => {
            statusEl.className = `status ${status}`;
            statusEl.innerHTML = message;
        };

        // Initialize Echo connection
        if (window.Echo) {
            addLog('🔌 Laravel Echo initialized', 'connection');
            
            // Set CSRF token for requests
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Listen for connection events
            window.Echo.connector.pusher.connection.bind('connected', () => {
                updateConnectionStatus('connected', '✅ Connected to WebSocket server');
                addLog('🟢 WebSocket connection established', 'connection');
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                updateConnectionStatus('disconnected', '❌ Disconnected from WebSocket server');
                addLog('🔴 WebSocket connection lost', 'error');
            });

            window.Echo.connector.pusher.connection.bind('connecting', () => {
                updateConnectionStatus('connecting', '🔄 Connecting to WebSocket server...');
            });

            // Error handling
            window.Echo.connector.pusher.connection.bind('error', (error) => {
                addLog(`❌ Connection error: ${error.error?.message || 'Unknown error'}`, 'error');
            });

            try {
                // Subscribe to project channel (public for testing)
                const projectChannel = window.Echo.channel(`project-public.${projectId}`);

                // Listen for task updates
                projectChannel.listen('.task.updated', (e) => {
                    addLog(`🟡 <strong>Task Updated:</strong> "${e.title}" <br>
                           📊 Status: <strong>${e.status}</strong> | Priority: ${e.priority} <br>
                           👤 Assignee: ${e.assignee ? e.assignee.name : 'Unassigned'}`, 'task-update');
                });

                // Listen for new comments
                projectChannel.listen('.comment.created', (e) => {
                    addLog(`💬 <strong>New Comment</strong> on task #${e.task_id}: ${e.task_title} <br>
                           💭 "${e.body}" <br>
                           👤 Author: <strong>${e.author.name}</strong>`, 'comment');
                });

                // Channel error handling
                projectChannel.error((error) => {
                    addLog(`❌ Channel error: ${error.message}`, 'error');
                });

                addLog(`📡 Subscribed to project-public.${projectId} channel`, 'connection');

            } catch (error) {
                addLog(`❌ Failed to subscribe to channel: ${error.message}`, 'error');
            }

        } else {
            addLog('❌ Laravel Echo not found! Make sure to build assets.', 'error');
            updateConnectionStatus('disconnected', '❌ Laravel Echo not available');
        }

        // Button event handlers
        document.getElementById('clear-log').addEventListener('click', () => {
            log.innerHTML = '<div class="log-entry connection">📋 Log cleared...</div>';
        });

        document.getElementById('test-connection').addEventListener('click', () => {
            const status = window.Echo?.connector?.pusher?.connection?.state || 'unknown';
            addLog(`🔍 Connection test - Current state: ${status}`, 'connection');
        });

        document.getElementById('create-test-task').addEventListener('click', () => {
            addLog('🔄 Triggering task update...', 'connection');
            // Simulate task update via API call
            fetch('/api/test-task-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                addLog('✅ Task update triggered', 'connection');
            }).catch(error => {
                addLog(`❌ Error: ${error.message}`, 'error');
            });
        });

        document.getElementById('create-test-comment').addEventListener('click', () => {
            addLog('🔄 Triggering comment creation...', 'connection');
            // Simulate comment creation via API call
            fetch('/api/test-comment-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(() => {
                addLog('✅ Comment creation triggered', 'connection');
            }).catch(error => {
                addLog(`❌ Error: ${error.message}`, 'error');
            });
        });
    </script>
</body>
</html>