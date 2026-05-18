function toggleAI(bookId = null) {
    const panel = document.getElementById('aiPanel');
    panel.classList.toggle('open');
    if(panel.classList.contains('open') && bookId) {
        fetchData(bookId);
    }
}

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

function smartType(elementId, text, speed = 10) {
    const element = document.getElementById(elementId);
    element.innerHTML = "";
    let i = 0;
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    type();
}

function fetchData(id) {
    const loader = document.getElementById('ai-loader');
    loader.style.display = 'block';
    
    // Clear old data so it doesn't look messy
    document.getElementById('sum-text').innerHTML = "";
    document.getElementById('point-list').innerHTML = "";
    document.getElementById('rec-list').innerHTML = "";
    document.getElementById('faq-list').innerHTML = "";

    fetch(`fetch_ai_data.php?book_id=${id}`)
        .then(res => res.json())
        .then(data => {
            setTimeout(() => {
                loader.style.display = 'none';
                
                // 1. Start the Typing Animation for Summary
                smartType('sum-text', data.summary);
                
                // 2. Immediately fill the other tabs (No typing here so they look full instantly)
                document.getElementById('point-list').innerHTML = data.key_points.map(p => 
                    `<li>${p}</li>`
                ).join('');
                
                document.getElementById('rec-list').innerHTML = data.recommendations.map(r => `
                    <div class="rec-card">📚 ${r}</div>
                `).join('');
                
                document.getElementById('faq-list').innerHTML = data.faqs.map(f => `
                    <div class="faq-item">
                        <div class="faq-q">Q: ${f.q}</div>
                        <div class="faq-a">${f.a}</div>
                    </div>
                `).join('');
            }, 600);
        });
}