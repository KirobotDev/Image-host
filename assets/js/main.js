document.addEventListener('DOMContentLoaded', function() {

    const fileInput = document.getElementById('fileInput');
    const progress = document.getElementById('progress');

    if (fileInput) {
        fileInput.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) {
                alert('Veuillez sélectionner une image valide.');
                return;
            }

            progress.style.display = 'block';
            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('upload.php', { method: 'POST', body: formData });
                const data = await response.json();
                progress.style.display = 'none';

                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.error || "Erreur lors de l'upload");
                }
            } catch (err) {
                progress.style.display = 'none';
                alert('Erreur : ' + err.message);
            }
        });
    }

    let userRole = 'guest';
    let lastMessageId = 0;

    async function checkRole() {
        try {
            const res = await fetch('chat_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'check_role'})
            });
            const data = await res.json();
            userRole = data.role || 'guest';
        } catch {
            userRole = 'guest';
        }
    }

    window.toggleChat = async function() {
        const chatWindow = document.getElementById('chatWindow');
        const authModal = document.getElementById('authModal');
        const chatInputContainer = document.getElementById('chatInputContainer');
        const chatFooter = document.getElementById('chatFooter');

        await checkRole();

        if (userRole === 'guest') {
            authModal.style.display = 'flex';
        } else {
            authModal.style.display = 'none';
            chatInputContainer.style.display = 'flex';
            chatFooter.style.display = 'flex';
            chatWindow.classList.toggle('active');
            if (chatWindow.classList.contains('active')) loadMessages(true);
        }
    };

    async function loadMessages(forceScroll = false) {
        try {
            const res = await fetch('chat_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get'})
            });
            const messages = await res.json();

            const chatMessages = document.getElementById('chatMessages');
            if (!chatMessages) return;

            const isNearBottom = chatMessages.scrollTop + chatMessages.clientHeight >= chatMessages.scrollHeight - 80;
            chatMessages.innerHTML = '';

            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'message';
                div.innerHTML = `
                    <img src="${encodeURI(msg.profile_pic)}" alt="Profil" class="chat-avatar" data-user-id="${msg.user_id}" style="width:30px; border-radius:50%; cursor:pointer;">
                    <div class="content">
                        <strong class="chat-username" data-user-id="${msg.user_id}" style="cursor:pointer;">${escapeHTML(msg.username)}</strong> 
                        <span class="chat-role">(${escapeHTML(msg.role)})</span>: ${escapeHTML(msg.message)}
                    </div>
                `;
                chatMessages.appendChild(div);
            });

            if (forceScroll || isNearBottom) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            if (messages.length > 0) {
                lastMessageId = messages[messages.length - 1].id;
            }

            document.querySelectorAll('.chat-avatar, .chat-username').forEach(el => {
                el.addEventListener('click', e => showUserPanel(e, el.dataset.userId));
            });

        } catch (err) {
            console.error('Erreur de chargement du chat:', err);
        }
    }

    const userPanel = document.createElement('div');
    userPanel.id = 'userPanel';
    userPanel.style.position = 'absolute';
    userPanel.style.display = 'none';
    userPanel.style.background = '#1a1a1a';
    userPanel.style.color = 'white';
    userPanel.style.padding = '10px';
    userPanel.style.borderRadius = '8px';
    userPanel.style.boxShadow = '0 4px 10px rgba(0,0,0,0.3)';
    userPanel.style.zIndex = '9999';
    userPanel.classList.add('user-menu-content');
    document.body.appendChild(userPanel);

    window.showUserPanel = function(e, userId) {
        e.stopPropagation();

        userPanel.innerHTML = '';

        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Fermer';
        closeBtn.onclick = closeUserPanel;
        userPanel.appendChild(closeBtn);

        if (['admin', 'staff', 'owner'].includes(userRole)) {
            const muteBtn = document.createElement('button');
            muteBtn.textContent = '🔇 Mute';
            muteBtn.onclick = () => { muteUser(userId); closeUserPanel(); };
            userPanel.appendChild(muteBtn);

            const deleteBtn = document.createElement('button');
            deleteBtn.textContent = '🗑️ Supprimer messages';
            deleteBtn.onclick = () => { deleteMessagesOfUser(userId); closeUserPanel(); };
            userPanel.appendChild(deleteBtn);
        }

        if (userRole === 'owner') {
            const setRoleBtn = document.createElement('button');
            setRoleBtn.textContent = '⚙️ Changer rôle';
            setRoleBtn.onclick = () => { showRoleOptions(userId); };
            userPanel.appendChild(setRoleBtn);
        }

        userPanel.style.left = e.pageX + 'px';
        userPanel.style.top = e.pageY + 'px';
        userPanel.style.display = 'flex';
    };

    function closeUserPanel() {
        userPanel.style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        if (!userPanel.contains(e.target)) closeUserPanel();
    });

    window.sendMessage = async function() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        if (!message) return;

        try {
            const res = await fetch('chat_backend.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'send', message})
            });
            const data = await res.json();
            if (data.success) {
                input.value = '';
                loadMessages(true);
            } else if (data.error === 'muted') {
                const botMsg = data.bot_message;
                if (botMsg) addBotMessage(botMsg.username, botMsg.role, botMsg.message);
            } else {
                alert(data.error || 'Erreur lors de l’envoi');
            }
        } catch (err) {
            alert('Erreur : ' + err.message);
        }
    };

    function addBotMessage(username, role, message) {
        const chatMessages = document.getElementById('chatMessages');
        const div = document.createElement('div');
        div.className = 'message bot-message';
        div.innerHTML = `
            <strong>${escapeHTML(username)} (${escapeHTML(role)})</strong>: ${escapeHTML(message)}
        `;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function deleteMessagesOfUser(userId) {
        const reason = prompt('Raison de la suppression :');
        if (!reason) return;

        const res = await fetch('chat_backend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete_all', user_id: userId, reason})
        });
        const data = await res.json();
        if (data.success) loadMessages();
        else alert('Erreur lors de la suppression');
    }

    async function muteUser(userId) {
        const minutes = parseInt(prompt('Durée du mute (1-10 minutes) :'));
        if (isNaN(minutes) || minutes < 1 || minutes > 10) {
            alert('Durée invalide (1-10 minutes)');
            return;
        }
        const reason = prompt('Raison du mute :');
        if (!reason) return;

        const res = await fetch('chat_backend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'mute', user_id: userId, minutes, reason})
        });
        const data = await res.json();
        if (data.success) alert('Utilisateur muté');
        else alert('Erreur lors du mute');
    }

    function showRoleOptions(userId) {
        const roleOptionsPanel = document.createElement('div');
        roleOptionsPanel.style.display = 'flex';
        roleOptionsPanel.style.flexDirection = 'column';
        roleOptionsPanel.style.gap = '5px';
        roleOptionsPanel.style.marginTop = '10px';

        const roles = ['support', 'staff', 'admin', '']; 
        roles.forEach(r => {
            const btn = document.createElement('button');
            btn.textContent = r === '' ? '❌ Retirer rôle' : `🎖️ ${r}`;
            btn.onclick = async () => {
                const res = await fetch('chat_backend.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'set_role', user_id: userId, new_role: r})
                });
                const data = await res.json();
                if (data.success) {
                    alert(r === '' ? 'Rôle retiré' : 'Rôle mis à jour');
                    updateUserBadge(userId, r);
                } else {
                    alert(data.error || 'Erreur lors de la mise à jour du rôle');
                }
                roleOptionsPanel.remove();
                closeUserPanel();
            };
            roleOptionsPanel.appendChild(btn);
        });

        userPanel.appendChild(roleOptionsPanel);
    }

    function updateUserBadge(userId, newRole) {
        document.querySelectorAll(`.chat-username[data-user-id="${userId}"]`).forEach(el => {
            const roleSpan = el.nextElementSibling;
            if (roleSpan && roleSpan.classList.contains('chat-role')) {
                roleSpan.textContent = newRole ? `(${escapeHTML(newRole)})` : '';
            }
        });
    }

    window.auth = async function(type) {
        const username = document.querySelector('#authForm [name="username"]').value;
        const password = document.querySelector('#authForm [name="password"]').value;

        const res = await fetch('auth.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: type, username: escapeHTML(username), password: escapeHTML(password)})
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('authModal').style.display = 'none';
            location.reload();
        } else {
            alert(data.error);
        }
    };

    window.closeAuthModal = function() {
        document.getElementById('authModal').style.display = 'none';
    };

    window.logout = async function() {
        const res = await fetch('logout.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Erreur lors de la déconnexion');
    };

    setInterval(() => {
        const chatWindow = document.getElementById('chatWindow');
        if (chatWindow && chatWindow.classList.contains('active')) {
            loadMessages(false);
        }
    }, 3000);

    function detectDevToolsAndAccess() {
        if (window.outerWidth - window.innerWidth > 100 || window.outerHeight - window.innerHeight > 100) {
            window.location.href = 'https://discord.gg/kirosb';
        }
        const path = window.location.pathname.toLowerCase();
        if (path.includes('.json') || path.includes('.env')) {
            window.location.href = 'https://discord.gg/kirosb';
        }
    }
    setInterval(detectDevToolsAndAccess, 500);

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') || (e.ctrlKey && e.key === 'U')) {
            e.preventDefault();
            window.location.href = 'https://discord.gg/kirosb';
        }
    });
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        window.location.href = 'https://discord.gg/kirosb';
    });

    function escapeHTML(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

});
