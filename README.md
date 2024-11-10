# Real-Time Quiz Application
## Introduction
This project is a real-time quiz application built using PHP with Workerman for handling WebSocket connections, along with a simple HTML/JavaScript frontend. The quiz allows users to join a quiz session using a unique Quiz ID and User ID, answer questions, and see their scores update in real-time on a leaderboard.

### Key Features:
- Real-time Question Display: Users are shown the same questions at the same time and can submit their answers.
- Real-time Leaderboard: Scores are updated live and a leaderboard is displayed as users answer questions.
- Quiz Flow: All users answer a question before moving to the next, and the quiz is only marked complete when all users have finished.
## How to Set Up?
Follow these steps to set up and run the Real-Time Quiz Application locally.

### Prerequisites
Before setting up, ensure you have the following installed:

```markdown
PHP (>= 7.4)
Composer (for managing PHP dependencies)
Node.js (for building the frontend if needed)
```

1. Clone the Repository
Start by cloning the repository to your local machine:

```markdown
bash
git clone https://github.com/hoangphammr/quiz-challenge.git
cd quiz-challenge
```

2. Install Dependencies
Use Composer to install PHP dependencies (including Workerman, which is used to handle WebSocket connections):

```markdown
bash
composer install
```

This will install all necessary packages defined in composer.json.

3. Start the WebSocket Server
Navigate to the project root and run the WebSocket server using Workerman:

```markdown
bash
php server.php
```

By default, the WebSocket server will run on port 9502. You can adjust the port in the server.php file if needed.

You should see output similar to:

```markdown
bash
Workerman started at http://0.0.0.0:9502
The server will now be ready to handle WebSocket connections.
```

4. Start the Web Server (Optional)
If you need a local server to serve the frontend HTML, you can use PHP’s built-in web server:

```markdown
bash
php -S localhost:8000
This will start a local server at http://localhost:8000, where the frontend can be accessed.
```

5. Open the Frontend
In your browser, navigate to the URL where your frontend is served (http://localhost:8000 by default). The frontend will allow you to enter your User ID and Quiz ID to join a quiz session.

6. Play the Quiz
Enter a User ID and a Quiz ID to join a quiz.
The first question will appear once you join, and you can select an answer.
After all users have answered a question, the next question will be displayed.
The leaderboard will update in real-time as users answer questions.

## Troubleshooting
Error: WebSocket connection failed

Make sure your WebSocket server (php server.php) is running.
Ensure no other services are using port 8080 (or whatever port you have set).
Error: Leaderboard not updating

Ensure that the WebSocket connection is working and that the backend is sending updates properly.
Check the browser console for any JavaScript errors.

## Conclusion
This project is a simple implementation of a real-time quiz application using WebSockets. It demonstrates how you can create interactive real-time applications with PHP and JavaScript. You can extend it further by adding more features like user authentication, multiple quizzes, and even adding different types of questions.

Feel free to fork this project, make improvements, and contribute!
