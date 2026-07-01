<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $background = htmlspecialchars($_POST['background']);
    $role = htmlspecialchars($_POST['target_role']);
    $pace = htmlspecialchars($_POST['pace']);

    // Enhanced prompt with better structure for YouTube recommendations
    $prompt = "Create a comprehensive step-by-step AI career roadmap for someone with a background in: $background 
    who wants to become a: $role. The learning pace should be: $pace.
    
    Please structure the roadmap with:
    1. Clear phases (Phase 1, Phase 2, etc.)
    2. Specific skills to learn in each phase
    3. Tools and frameworks to master
    4. Practical projects to build
    5. YouTube video suggestions with SPECIFIC video titles and channel names
    6. Time estimates for each phase
    
    IMPORTANT: For YouTube recommendations, provide ACTUAL specific video titles and channel names that exist on YouTube. 
    Format YouTube suggestions like this:
    
    YouTube Video Suggestions:
    - 'Complete React Tutorial' by The Net Ninja
    - 'JavaScript Mastery' by Traversy Media
    - 'CSS Grid Layout' by Kevin Powell
    - 'TypeScript Crash Course' by Programming with Mosh
    - 'Frontend Interview Questions' by Clement Mihailescu";

    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            ["role" => "system", "content" => "You are a professional AI career coach and technical mentor. Create detailed, actionable roadmaps with specific resource recommendations. Always provide REAL YouTube video titles and channel names that actually exist. Never leave YouTube suggestions blank."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 2000
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $OPENAI_API_KEY"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "CURL Error: " . curl_error($ch);
        exit;
    }
    curl_close($ch);

    $result = json_decode($response, true);

    // Start the enhanced output with beautiful UI
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Your AI Career Roadmap</title>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <style>
        :root {
          --primary: #2563eb;
          --primary-dark: #1d4ed8;
          --secondary: #7c3aed;
          --accent: #ec4899;
          --light: #f8fafc;
          --dark: #1e293b;
          --gray: #64748b;
          --success: #06d6a0;
          --border-radius: 16px;
          --box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
          --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: "Inter", "Segoe UI", system-ui, sans-serif;
        }

        body {
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
          color: var(--dark);
          min-height: 100vh;
          padding: 20px;
          line-height: 1.7;
        }

        .roadmap-container {
          max-width: 1200px;
          margin: 0 auto;
          background: white;
          border-radius: var(--border-radius);
          box-shadow: var(--box-shadow);
          overflow: hidden;
          animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
          from { opacity: 0; transform: translateY(40px); }
          to { opacity: 1; transform: translateY(0); }
        }

        .roadmap-header {
          background: linear-gradient(135deg, var(--primary), var(--secondary));
          color: white;
          padding: 50px 40px;
          text-align: center;
          position: relative;
          overflow: hidden;
        }

        .roadmap-header::before {
          content: "";
          position: absolute;
          top: -50%;
          left: -50%;
          width: 200%;
          height: 200%;
          background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
          transform: rotate(30deg);
        }

        .roadmap-header h1 {
          font-size: 2.8rem;
          margin-bottom: 15px;
          font-weight: 700;
          position: relative;
        }

        .roadmap-header p {
          font-size: 1.2rem;
          opacity: 0.9;
          position: relative;
          margin-bottom: 30px;
        }

        .user-info {
          display: flex;
          justify-content: center;
          gap: 25px;
          margin-top: 20px;
          flex-wrap: wrap;
          position: relative;
        }

        .info-card {
          background: rgba(255, 255, 255, 0.15);
          backdrop-filter: blur(10px);
          padding: 15px 25px;
          border-radius: 50px;
          font-size: 0.95rem;
          display: flex;
          align-items: center;
          gap: 8px;
          border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .roadmap-content {
          padding: 50px;
          background: var(--light);
        }

        .phase {
          background: white;
          border-radius: var(--border-radius);
          padding: 35px;
          margin: 35px 0;
          box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
          border-left: 5px solid var(--primary);
          transition: var(--transition);
        }

        .phase:hover {
          transform: translateY(-5px);
          box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .phase-header {
          display: flex;
          align-items: center;
          margin-bottom: 25px;
          padding-bottom: 20px;
          border-bottom: 2px solid #f1f5f9;
        }

        .phase-number {
          background: linear-gradient(135deg, var(--primary), var(--secondary));
          color: white;
          width: 50px;
          height: 50px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.3rem;
          font-weight: 700;
          margin-right: 20px;
          flex-shrink: 0;
        }

        .phase-title {
          font-size: 1.6rem;
          color: var(--dark);
          font-weight: 700;
        }

        .phase-duration {
          margin-left: auto;
          background: var(--success);
          color: white;
          padding: 8px 16px;
          border-radius: 25px;
          font-size: 0.9rem;
          font-weight: 600;
        }

        .skills-section, .tools-section, .projects-section, .youtube-section {
          margin: 25px 0;
        }

        .section-title {
          font-size: 1.2rem;
          color: var(--primary);
          margin-bottom: 15px;
          display: flex;
          align-items: center;
          gap: 10px;
          font-weight: 600;
        }

        .youtube-section .section-title {
          color: #ff0000;
        }

        .skills-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 12px;
          margin: 15px 0;
        }

        .skill-item {
          background: var(--light);
          padding: 12px 18px;
          border-radius: 10px;
          border-left: 3px solid var(--primary);
          font-weight: 500;
        }

        .tools-list {
          display: flex;
          flex-wrap: wrap;
          gap: 10px;
          margin: 15px 0;
        }

        .tool-item {
          background: linear-gradient(135deg, var(--primary), var(--secondary));
          color: white;
          padding: 10px 18px;
          border-radius: 25px;
          font-size: 0.9rem;
          font-weight: 500;
        }

        .project-card {
          background: white;
          border: 1px solid #e2e8f0;
          border-radius: 12px;
          padding: 20px;
          margin: 15px 0;
          transition: var(--transition);
        }

        .project-card:hover {
          border-color: var(--primary);
          box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .project-title {
          font-size: 1.1rem;
          font-weight: 600;
          color: var(--dark);
          margin-bottom: 8px;
        }

        .youtube-cards-container {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
          gap: 20px;
          margin: 20px 0;
        }

        .youtube-card {
          background: white;
          border: 1px solid #e2e8f0;
          border-radius: 12px;
          padding: 20px;
          display: flex;
          align-items: flex-start;
          gap: 15px;
          transition: var(--transition);
          cursor: pointer;
          height: 100%;
        }

        .youtube-card:hover {
          border-color: #ff0000;
          transform: translateY(-3px);
          box-shadow: 0 10px 25px rgba(255, 0, 0, 0.1);
        }

        .youtube-icon {
          color: #ff0000;
          font-size: 1.8rem;
          flex-shrink: 0;
          margin-top: 2px;
        }

        .youtube-content {
          flex: 1;
        }

        .youtube-content h4 {
          color: var(--dark);
          margin-bottom: 8px;
          font-weight: 600;
          font-size: 1rem;
        }

        .youtube-content .channel {
          color: var(--gray);
          font-size: 0.9rem;
          margin-bottom: 10px;
          font-style: italic;
        }

        .youtube-content .description {
          color: var(--gray);
          font-size: 0.85rem;
          margin-bottom: 12px;
          line-height: 1.5;
        }

        .youtube-link {
          color: var(--primary);
          font-size: 0.85rem;
          font-weight: 500;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          gap: 5px;
          padding: 6px 12px;
          border: 1px solid var(--primary);
          border-radius: 6px;
          transition: var(--transition);
        }

        .youtube-link:hover {
          background: var(--primary);
          color: white;
          text-decoration: none;
        }

        .action-buttons {
          display: flex;
          justify-content: center;
          gap: 20px;
          margin-top: 50px;
          flex-wrap: wrap;
        }

        .btn {
          padding: 16px 32px;
          border: none;
          border-radius: var(--border-radius);
          font-size: 1rem;
          font-weight: 600;
          cursor: pointer;
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 10px;
          text-decoration: none;
        }

        .btn-primary {
          background: linear-gradient(135deg, var(--primary), var(--secondary));
          color: white;
        }

        .btn-outline {
          background: transparent;
          border: 2px solid var(--primary);
          color: var(--primary);
        }

        .btn:hover {
          transform: translateY(-3px);
          box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .error-container {
          text-align: center;
          padding: 60px 40px;
        }

        .error-icon {
          font-size: 4rem;
          color: #ef4444;
          margin-bottom: 25px;
        }

        .roadmap-content h2 {
          color: var(--primary);
          margin: 40px 0 20px;
          padding-bottom: 15px;
          border-bottom: 2px solid #f1f5f9;
          font-size: 1.8rem;
        }

        .roadmap-content h3 {
          color: var(--secondary);
          margin: 30px 0 15px;
          font-size: 1.4rem;
        }

        .roadmap-content ul, .roadmap-content ol {
          margin-left: 25px;
          margin-bottom: 25px;
        }

        .roadmap-content li {
          margin-bottom: 10px;
          padding-left: 8px;
        }

        .fallback-youtube {
          background: #fff5f5;
          border: 1px solid #fed7d7;
          border-radius: 12px;
          padding: 25px;
          margin: 20px 0;
        }

        .fallback-youtube h4 {
          color: #e53e3e;
          margin-bottom: 15px;
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .fallback-links {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
          gap: 15px;
        }

        .fallback-link {
          background: white;
          padding: 15px;
          border-radius: 8px;
          border: 1px solid #e2e8f0;
          text-decoration: none;
          color: var(--dark);
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .fallback-link:hover {
          border-color: var(--primary);
          transform: translateY(-2px);
        }

        @media (max-width: 768px) {
          .roadmap-header {
            padding: 40px 25px;
          }
          
          .roadmap-header h1 {
            font-size: 2.2rem;
          }
          
          .roadmap-content {
            padding: 30px 20px;
          }
          
          .user-info {
            flex-direction: column;
            gap: 15px;
          }
          
          .phase-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
          }
          
          .phase-duration {
            margin-left: 0;
          }
          
          .action-buttons {
            flex-direction: column;
          }
          
          .skills-grid {
            grid-template-columns: 1fr;
          }
          
          .youtube-cards-container {
            grid-template-columns: 1fr;
          }
        }
      </style>
    </head>
    <body>
      <div class="roadmap-container">
        <div class="roadmap-header">
          <h1><i class="fas fa-road"></i> Your AI Career Roadmap</h1>
          <p>Personalized guide to becoming a ' . htmlspecialchars($role) . '</p>
          <div class="user-info">
            <div class="info-card"><i class="fas fa-user-graduate"></i> Background: ' . htmlspecialchars($background) . '</div>
            <div class="info-card"><i class="fas fa-bullseye"></i> Target Role: ' . htmlspecialchars($role) . '</div>
            <div class="info-card"><i class="fas fa-tachometer-alt"></i> Learning Pace: ' . ucfirst($pace) . '</div>
          </div>
        </div>
        <div class="roadmap-content">';

    if (isset($result['choices'][0]['message']['content'])) {
        $output = $result['choices'][0]['message']['content'];
        
        // Convert the plain text to HTML with better formatting
        $output = nl2br(htmlspecialchars($output));
        
        // Enhanced formatting for phases
        $output = preg_replace('/(Phase \d+:|Stage \d+:|Phase \d+)/i', '</div><div class="phase"><div class="phase-header"><div class="phase-number">${1}</div><div class="phase-title">${1}</div><div class="phase-duration">' . getPhaseDuration($pace) . '</div></div>', $output);
        
        // Format skills sections
        $output = preg_replace('/(Skills:|Key Skills:)/i', '<div class="skills-section"><div class="section-title"><i class="fas fa-brain"></i>${1}</div>', $output);
        
        // Format tools sections
        $output = preg_replace('/(Tools:|Frameworks:|Technologies:)/i', '<div class="tools-section"><div class="section-title"><i class="fas fa-tools"></i>${1}</div>', $output);
        
        // Format projects sections
        $output = preg_replace('/(Projects:|Example Projects:)/i', '<div class="projects-section"><div class="section-title"><i class="fas fa-project-diagram"></i>${1}</div>', $output);
        
        // Enhanced YouTube Video Suggestions formatting
        $output = processYouTubeSuggestions($output, $role);
        
        // Clean up any duplicate divs
        $output = str_replace('</div><div class="phase">', '<div class="phase">', $output);
        
        echo $output;
        
        // Add fallback YouTube recommendations if none were found
        if (strpos($output, 'youtube-section') === false) {
            echo generateFallbackYouTubeRecommendations($role);
        }
        
    } else {
        echo '<div class="error-container">
                <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h2>Something Went Wrong</h2>
                <p>We encountered an issue generating your roadmap. Please check your API key or try again later.</p>
                <div style="margin-top: 30px; background: #f8fafc; padding: 20px; border-radius: var(--border-radius); text-align: left;">
                  <h3>Technical Details:</h3>
                  <pre style="white-space: pre-wrap; font-size: 0.8rem; background: white; padding: 15px; border-radius: 8px;">' . htmlspecialchars($response) . '</pre>
                </div>
              </div>';
    }

    echo '</div>
          <div class="action-buttons">
            <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print"></i> Print Roadmap</button>
            <a href="index.html" class="btn btn-outline"><i class="fas fa-redo"></i> Generate Another Roadmap</a>
          </div>
        </div>
      </div>
      
      <script>
        // Add click handlers for YouTube cards
        document.addEventListener("DOMContentLoaded", function() {
          const youtubeCards = document.querySelectorAll(".youtube-card");
          youtubeCards.forEach(card => {
            card.addEventListener("click", function(e) {
              if (e.target.tagName !== "A" && !e.target.classList.contains("youtube-link")) {
                const link = this.querySelector(".youtube-link");
                if (link) {
                  window.open(link.href, "_blank");
                }
              }
            });
          });
        });
      </script>
    </body>
    </html>';
}

// Helper function to estimate phase duration based on pace
function getPhaseDuration($pace) {
    switch($pace) {
        case 'slow':
            return '4-6 weeks';
        case 'medium':
            return '2-4 weeks';
        case 'fast':
            return '1-2 weeks';
        default:
            return '2-4 weeks';
    }
}

// Process YouTube suggestions with better formatting
function processYouTubeSuggestions($output, $role) {
    // Multiple patterns to catch YouTube suggestions
    $patterns = [
        '/(YouTube Video Suggestions:|YouTube Recommendations:|Recommended Videos:|YouTube Resources:)(.*?)(?=(Phase|\n\n|\<\/div\>|$))/is',
        '/(YouTube:)(.*?)(?=(Phase|\n\n|\<\/div\>|$))/is'
    ];
    
    foreach ($patterns as $pattern) {
        $output = preg_replace_callback($pattern, function($matches) use ($role) {
            $youtubeContent = trim($matches[2]);
            
            // Extract video titles and channels using various patterns
            preg_match_all('/(?:[-•*]?\s*)?[\'"]([^\'"]+)[\'"](?:\s*(?:by|from)\s*([^\n<]+))?/i', $youtubeContent, $videoMatches, PREG_SET_ORDER);
            
            $youtubeCards = '';
            
            if (!empty($videoMatches)) {
                foreach ($videoMatches as $video) {
                    $title = trim($video[1]);
                    $channel = isset($video[2]) ? trim($video[2]) : 'Various Creators';
                    
                    // Clean up channel name
                    $channel = preg_replace('/[.,]?\s*$/', '', $channel);
                    
                    if (!empty($title)) {
                        $searchQuery = urlencode($title . ' ' . $channel . ' tutorial');
                        $youtubeCards .= '
                        <div class="youtube-card">
                            <div class="youtube-icon"><i class="fab fa-youtube"></i></div>
                            <div class="youtube-content">
                                <h4>' . htmlspecialchars($title) . '</h4>
                                <div class="channel">by ' . htmlspecialchars($channel) . '</div>
                                <div class="description">Recommended learning resource for ' . htmlspecialchars($role) . '</div>
                                <a href="https://www.youtube.com/results?search_query=' . $searchQuery . '" target="_blank" class="youtube-link">
                                    <i class="fas fa-external-link-alt"></i> Watch on YouTube
                                </a>
                            </div>
                        </div>';
                    }
                }
            }
            
            // If no specific videos found, try to extract from bullet points
            if (empty($youtubeCards)) {
                $lines = preg_split('/\n/', $youtubeContent);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && !preg_match('/^\s*$/', $line)) {
                        // Clean the line and create a meaningful search
                        $cleanLine = preg_replace('/^[-•*]\s*/', '', $line);
                        if (strlen($cleanLine) > 10) { // Only use substantial lines
                            $searchQuery = urlencode($cleanLine . ' ' . $role . ' tutorial');
                            $youtubeCards .= '
                            <div class="youtube-card">
                                <div class="youtube-icon"><i class="fab fa-youtube"></i></div>
                                <div class="youtube-content">
                                    <h4>' . htmlspecialchars($cleanLine) . '</h4>
                                    <div class="channel">Recommended Channel</div>
                                    <div class="description">Learning resource for ' . htmlspecialchars($role) . '</div>
                                    <a href="https://www.youtube.com/results?search_query=' . $searchQuery . '" target="_blank" class="youtube-link">
                                        <i class="fas fa-external-link-alt"></i> Watch on YouTube
                                    </a>
                                </div>
                            </div>';
                        }
                    }
                }
            }
            
            if (!empty($youtubeCards)) {
                return '<div class="youtube-section">
                        <div class="section-title">
                            <i class="fab fa-youtube" style="color: #ff0000;"></i>
                            YouTube Video Suggestions
                        </div>
                        <div class="youtube-cards-container">
                            ' . $youtubeCards . '
                        </div>
                    </div>';
            }
            
            return $matches[0];
        }, $output);
    }
    
    return $output;
}

// Generate fallback YouTube recommendations based on role
function generateFallbackYouTubeRecommendations($role) {
    $fallbackVideos = [
        'Frontend Developer' => [
            ['title' => 'JavaScript Tutorial for Beginners', 'channel' => 'freeCodeCamp.org'],
            ['title' => 'React JS Course for Beginners', 'channel' => 'The Net Ninja'],
            ['title' => 'CSS Grid Layout Crash Course', 'channel' => 'Traversy Media'],
            ['title' => 'TypeScript Tutorial for Beginners', 'channel' => 'Programming with Mosh'],
            ['title' => 'Frontend Development Roadmap 2024', 'channel' => 'Coder Coder']
        ],
        'AI Engineer' => [
            ['title' => 'Machine Learning Tutorial Python', 'channel' => 'freeCodeCamp.org'],
            ['title' => 'Deep Learning Fundamentals', 'channel' => 'Sentdex'],
            ['title' => 'PyTorch for Deep Learning', 'channel' => 'Python Engineer'],
            ['title' => 'TensorFlow 2.0 Complete Course', 'channel' => 'freeCodeCamp.org'],
            ['title' => 'AI Engineering Roadmap', 'channel' => 'Daniel Bourke']
        ],
        'Data Scientist' => [
            ['title' => 'Data Science Full Course', 'channel' => 'freeCodeCamp.org'],
            ['title' => 'Python for Data Science', 'channel' => 'Keith Galli'],
            ['title' => 'Machine Learning with Scikit-Learn', 'channel' => 'StatQuest with Josh Starmer'],
            ['title' => 'Data Visualization with Python', 'channel' => 'Ken Jee'],
            ['title' => 'SQL for Data Science', 'channel' => 'Alex The Analyst']
        ],
        'ML Engineer' => [
            ['title' => 'Machine Learning Engineering Course', 'channel' => 'freeCodeCamp.org'],
            ['title' => 'MLOps Complete Course', 'channel' => 'The AI & ML Channel'],
            ['title' => 'Deploying ML Models', 'channel' => 'Venelin Valkov'],
            ['title' => 'Machine Learning System Design', 'channel' => 'ByteByteGo'],
            ['title' => 'ML Engineering Roadmap', 'channel' => 'Boris Meinardus']
        ]
    ];
    
    // Default videos if role not found
    $defaultVideos = [
        ['title' => 'Programming Tutorials Playlist', 'channel' => 'freeCodeCamp.org'],
        ['title' => 'Career Development Guide', 'channel' => 'Traversy Media'],
        ['title' => 'Technical Skills Roadmap', 'channel' => 'Programming with Mosh'],
        ['title' => 'Project Building Tutorials', 'channel' => 'The Net Ninja'],
        ['title' => 'Interview Preparation Guide', 'channel' => 'Clément Mihailescu']
    ];
    
    $videos = $fallbackVideos[$role] ?? $defaultVideos;
    
    $youtubeCards = '';
    foreach ($videos as $video) {
        $searchQuery = urlencode($video['title'] . ' ' . $video['channel']);
        $youtubeCards .= '
        <div class="youtube-card">
            <div class="youtube-icon"><i class="fab fa-youtube"></i></div>
            <div class="youtube-content">
                <h4>' . htmlspecialchars($video['title']) . '</h4>
                <div class="channel">by ' . htmlspecialchars($video['channel']) . '</div>
                <div class="description">Recommended learning resource for ' . htmlspecialchars($role) . '</div>
                <a href="https://www.youtube.com/results?search_query=' . $searchQuery . '" target="_blank" class="youtube-link">
                    <i class="fas fa-external-link-alt"></i> Watch on YouTube
                </a>
            </div>
        </div>';
    }
    
    return '
    <div class="youtube-section">
        <div class="section-title">
            <i class="fab fa-youtube" style="color: #ff0000;"></i>
            Recommended YouTube Learning Resources
        </div>
        <div class="youtube-cards-container">
            ' . $youtubeCards . '
        </div>
    </div>';
}
?>