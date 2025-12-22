(function(){
  const el = document.querySelector('[data-ugc-app]');
  if(!el) return;

  const state = {
    mode: 'latest',
    page: 1,
    per_page: 10,
    loading: false,
    visitor_uuid: null,
    myProfile: null,
    activePostForComments: null,
  };

  function uuidv4(){
    // Simple UUID-ish generator (no crypto requirement for MVP)
    const s = () => Math.floor((1+Math.random())*0x10000).toString(16).substring(1);
    return `${s()}${s()}-${s()}-${s()}-${s()}-${s()}${s()}${s()}`;
  }

  function getVisitorUUID(){
    let v = localStorage.getItem('ugc_visitor_uuid');
    if(!v){
      v = uuidv4().replace(/[^a-zA-Z0-9\-_]/g,'');
      localStorage.setItem('ugc_visitor_uuid', v);
    }
    return v;
  }

  state.visitor_uuid = getVisitorUUID();

  async function api(path, opts={}){
    const headers = Object.assign({
      'Content-Type': 'application/json',
      'X-WP-Nonce': UGC_SOCIAL.nonce,
      'X-UGC-Visitor': state.visitor_uuid
    }, opts.headers || {});
    const res = await fetch(`${UGC_SOCIAL.restUrl}${path}`, Object.assign({}, opts, {headers}));
    const data = await res.json().catch(()=> ({}));
    if(!res.ok){
      const msg = (data && data.message) ? data.message : 'Erreur.';
      throw new Error(msg);
    }
    return data;
  }

  async function uploadMedia(file){
    // Upload to custom UGC endpoint that bypasses WordPress image validation
    const form = new FormData();
    form.append('file', file);
    const res = await fetch(`${UGC_SOCIAL.restUrl}/upload`, {
      method: 'POST',
      headers: {
        'X-WP-Nonce': UGC_SOCIAL.nonce,
        'X-UGC-Visitor': state.visitor_uuid
      },
      body: form
    });
    const data = await res.json().catch(()=> ({}));
    if(!res.ok){
      throw new Error((data && data.message) ? data.message : 'Upload média impossible.');
    }
    return data; // includes id, source_url
  }

  function qs(sel, root=document){ if(!root) return null; return root.querySelector(sel); }
  function qsa(sel, root=document){ if(!root) return []; return Array.from(root.querySelectorAll(sel)); }

  const feedEl = qs('[data-ugc-feed]', el);
  const composerModal = qs('[data-modal="composer"]', el);
  const commentsModal = qs('[data-modal="comments"]', el);
  const profileModal = qs('[data-modal="profile"]', el);
  const errorEl = qs('[data-error]', el);
  const commentsErrorEl = qs('[data-comments-error]', el);
  const profileErrorEl = qs('[data-profile-error]', el);
  const commentsEl = qs('[data-comments]', el);

  const mePromptEl = qs('[data-me-prompt]', el);
  const meAvatarHost = qs('[data-me-avatar]', el);

  const authBlock = qs('[data-auth-block]', composerModal);
  const authInfo = qs('[data-auth-info]', composerModal);
  const authMeAvatar = qs('[data-auth-me-avatar]', composerModal);
  const authMeName = qs('[data-auth-me-name]', composerModal);
  const postActions = qs('[data-post-actions]', composerModal);

  function renderAvatarHost(url){
    if(!meAvatarHost) return;
    const safe = url || '';
    meAvatarHost.innerHTML = safe ? `<img class="ugc-avatar" src="${safe}" alt="" />` : `<div class="ugc-avatar ugc-avatar--placeholder"></div>`;
  }

  function setComposerIdentityUI(){
    if(state.myProfile){
      if(mePromptEl) mePromptEl.textContent = `Quoi de neuf, ${state.myProfile.display_name} ?`;
      renderAvatarHost(state.myProfile.avatar_url);
    } else {
      if(mePromptEl) mePromptEl.textContent = `Quoi de neuf ?`;
      renderAvatarHost(null);
    }
  }

  function showAuthBlock(show){
    if(authBlock) authBlock.style.display = show ? 'grid' : 'none';
    if(authInfo) authInfo.style.display = show ? 'none' : 'block';
    if(!show && state.myProfile){
      if(authMeAvatar) authMeAvatar.src = state.myProfile.avatar_url;
      if(authMeName) authMeName.textContent = state.myProfile.display_name;
    }
  }

  function configureMediaPicker(kind){
    const mediaInput = qs('[data-input="media_file"]', composerModal);
    if(!mediaInput) return;
    mediaInput.value = '';
    mediaInput.removeAttribute('capture');

    if(kind === 'video'){
      mediaInput.accept = 'video/mp4,video/webm,video/*';
    } else {
      mediaInput.accept = 'image/*';
      if(kind === 'camera'){
        // Mobile hint to open camera
        mediaInput.setAttribute('capture', 'environment');
      }
    }
  }

  function __ugc_showComposerStep(step){
    const authStep = qs('[data-step="auth"]', composerModal);
    const postStep = qs('[data-step="post"]', composerModal);
    const postActions = qs('[data-post-actions]', composerModal);

    if(authStep) authStep.style.display = (step === 'auth') ? 'block' : 'none';
    if(postStep) postStep.style.display = (step === 'post') ? 'block' : 'none';
    if(postActions) postActions.style.display = (step === 'post') ? 'flex' : 'none';
  }

  window.showComposerStep = __ugc_showComposerStep;


  function resetComposerStep(){
    window.showComposerStep('auth');
  }

  function openModal(modal){ modal.setAttribute('aria-hidden','false'); modal.classList.add('is-open'); }
  function closeModal(modal){ modal.setAttribute('aria-hidden','true'); modal.classList.remove('is-open'); }

  function setError(target, msg){
    target.textContent = msg || '';
    target.style.display = msg ? 'block' : 'none';
  }

  function renderPostCard(item){
    const mediaHtml = (() => {
      if(!item.media || !item.media.length) return '';
      const m = item.media[0];
      if(m.is_video){
        return `<video class="ugc-media" controls playsinline src="${m.url}"></video>`;
      }
      return `<img class="ugc-media" src="${m.url}" alt="" loading="lazy" />`;
    })();

    // Check if current user is the author
    const isMyPost = state.myProfile && item.author.id === state.myProfile.id;
    const actionsMenu = isMyPost ? `
      <div class="ugc-post-menu">
        <button class="ugc-post-menu-btn" data-action="edit-post" title="Modifier">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
          </svg>
        </button>
        <button class="ugc-post-menu-btn ugc-post-menu-btn--delete" data-action="delete-post" title="Supprimer">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
        </button>
      </div>
    ` : '';

    return `
      <div class="ugc-card" data-post-id="${item.id}">
        <div class="ugc-card__head">
          <img class="ugc-avatar" src="${item.author.avatar_url}" alt="" />
          <div class="ugc-author">
            <div class="ugc-author__name">${escapeHtml(item.author.display_name)}</div>
            <div class="ugc-author__date">${new Date(item.date).toLocaleString()}</div>
          </div>
          ${actionsMenu}
        </div>
        ${mediaHtml}
        ${item.caption ? `<div class="ugc-caption">${formatHashtags(item.caption)}</div>` : ``}
        <div class="ugc-actions">
          <button class="ugc-btn ugc-btn-like ${item.liked_by_me ? 'is-liked':''}" data-action="like">
            <svg class="ugc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <span data-like-count>${item.like_count}</span>
          </button>
          <button class="ugc-btn" data-action="comments">
            <svg class="ugc-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <span>${item.comment_count}</span>
          </button>
        </div>
      </div>
    `;
  }

  function escapeHtml(str){
    return String(str||'').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }

  function formatHashtags(text){
    if(!text) return '';
    // Escape HTML first
    const escaped = escapeHtml(text);
    // Then replace hashtags with styled version
    return escaped.replace(/#(\w+)/g, '<span class="ugc-hashtag">#$1</span>');
  }

  async function loadFeed(reset=false){
    if(state.loading) return;
    state.loading = true;
    try{
      if(reset){
        state.page = 1;
        feedEl.innerHTML = '';
      }
      const data = await api(`/feed?mode=${encodeURIComponent(state.mode)}&page=${state.page}&per_page=${state.per_page}`);
      const html = data.items.map(renderPostCard).join('');
      feedEl.insertAdjacentHTML('beforeend', html);
    }catch(e){
      // silent for feed
      console.warn(e);
    }finally{
      state.loading = false;
    }
  }

  async function ensureProfileFromComposer(){
    if(state.myProfile) return state.myProfile;

    const display_name = (qs('[data-input="display_name"]', composerModal).value || '').trim();
    const pin = (qs('[data-input="pin"]', composerModal).value || '').trim();
    const avatarFile = qs('[data-input="avatar_file"]', composerModal).files[0];

    if(!display_name) throw new Error('Pseudonyme requis.');
    if(!/^[0-9]{4,6}$/.test(pin)) throw new Error('PIN invalide (4–6 chiffres).');

    let avatar_attachment_id = null;
    if(avatarFile){
      const up = await uploadMedia(avatarFile);
      avatar_attachment_id = up.id;
    }

    const me = await api('/profile/upsert', {
      method: 'POST',
      body: JSON.stringify({ display_name, pin, avatar_attachment_id })
    });
    state.myProfile = me;
    return me;
  }

  async function submitPost(){
    setError(errorEl, '');
    const caption = (qs('[data-input="caption"]', composerModal).value || '').trim();
    const mediaFile = qs('[data-input="media_file"]', composerModal).files[0];

    try{
      // Check if we're editing an existing post
      const isEditing = !!state.editingPostId;

      if(!isEditing){
        await ensureProfileFromComposer();
      }

      let media_ids = [];
      let media_type = 'none';

      if(mediaFile){
        const up = await uploadMedia(mediaFile);
        media_ids = [up.id];
        media_type = (up.mime_type && up.mime_type.startsWith('video/')) ? 'video' : 'image';
      }

      if(isEditing){
        // Update existing post
        await api(`/posts/${state.editingPostId}`, {
          method: 'PUT',
          body: JSON.stringify({ caption, media_type, media_ids: mediaFile ? media_ids : undefined })
        });

        // Clear editing state
        state.editingPostId = null;

        // Reset button text
        const submitBtn = qs('[data-action="submit-post"]', composerModal);
        if(submitBtn) submitBtn.textContent = 'Publier';
      } else {
        // Create new post
        await api('/posts', {
          method: 'POST',
          body: JSON.stringify({ caption, media_type, media_ids })
        });
      }

      closeModal(composerModal);
      resetComposerStep();
      // reset composer fields
      qs('[data-input="caption"]', composerModal).value = '';
      qs('[data-input="media_file"]', composerModal).value = '';
      // keep pseudo/pin for convenience

      // Refresh feed
      loadFeed(true);
      // fallback auto-load in case of cached event handlers
      setTimeout(()=>{
        try{
          const feed = qs('[data-ugc-feed]', el);
          if(feed && !feed.children.length){
            loadFeed(true);
          }
        }catch(e){}
      }, 800);
    }catch(e){
      setError(errorEl, e.message || 'Erreur.');
    }
  }

  function createFlyingHeart(x, y, card){
    const heart = document.createElement('div');
    heart.className = 'ugc-flying-heart';
    heart.textContent = '❤️';
    heart.style.left = `${x}px`;
    heart.style.top = `${y}px`;

    // Random slight horizontal movement
    const randomX = (Math.random() - 0.5) * 40;
    heart.style.setProperty('--random-x', `${randomX}px`);

    card.appendChild(heart);

    // Remove after animation
    setTimeout(() => {
      heart.remove();
    }, 2000);
  }

  async function toggleLike(postId, btn){
    try{
      const res = await api('/like/toggle', { method:'POST', body: JSON.stringify({post_id: postId}) });
      const countEl = btn.querySelector('[data-like-count]');
      if(countEl) countEl.textContent = String(res.like_count);
      btn.classList.toggle('is-liked', !!res.liked);

      // Flying hearts animation when liked
      if(res.liked){
        const card = btn.closest('[data-post-id]');
        if(card){
          const rect = btn.getBoundingClientRect();
          const cardRect = card.getBoundingClientRect();

          // Create multiple hearts
          for(let i = 0; i < 5; i++){
            setTimeout(() => {
              const x = rect.left - cardRect.left + (Math.random() * 30);
              const y = rect.top - cardRect.top + (Math.random() * 20);
              createFlyingHeart(x, y, card);
            }, i * 100);
          }
        }
      }
    }catch(e){
      alert(e.message || 'Erreur like.');
    }
  }

  async function openComments(postId){
    state.activePostForComments = postId;
    setError(commentsErrorEl, '');
    commentsEl.innerHTML = '';
    if(!commentsModal) return;
      openModal(commentsModal);

    try{
      const data = await fetch(`${UGC_SOCIAL.restUrl}/comments?post_id=${postId}&page=1&per_page=50`).then(r=>r.json());
      const items = (data && data.items) ? data.items : [];
      commentsEl.innerHTML = items.map(c => `
        <div class="ugc-comment">
          <img class="ugc-avatar ugc-avatar--sm" src="${c.author.avatar_url}" alt="" />
          <div class="ugc-comment__body">
            <div class="ugc-comment__meta"><strong>${escapeHtml(c.author.display_name)}</strong> · ${new Date(c.date).toLocaleString()}</div>
            <div class="ugc-comment__text">${formatHashtags(c.content)}</div>
          </div>
        </div>
      `).join('');
    }catch(e){
      setError(commentsErrorEl, 'Impossible de charger les commentaires.');
    }
  }

  async function submitComment(){
    setError(commentsErrorEl,'');
    const text = (qs('[data-input="comment_text"]', commentsModal).value || '').trim();
    if(!text) return setError(commentsErrorEl, 'Commentaire vide.');

    try{
      // if no profile yet, force quick auth via modal
      if(!state.myProfile){
        setError(commentsErrorEl, 'Veuillez d\'abord définir votre pseudonyme et PIN (une seule fois).');
        // Open composer modal but only show identity block
        closeModal(commentsModal);
        if(!composerModal) return;
      openModal(composerModal);
        showAuthBlock(true);
      window.showComposerStep('auth');
        return;
      }

      const res = await api('/comment', { method:'POST', body: JSON.stringify({ post_id: state.activePostForComments, content: text })});
      qs('[data-input="comment_text"]', commentsModal).value = '';

      // Append in UI
      commentsEl.insertAdjacentHTML('beforeend', `
        <div class="ugc-comment">
          <img class="ugc-avatar ugc-avatar--sm" src="${res.author.avatar_url}" alt="" />
          <div class="ugc-comment__body">
            <div class="ugc-comment__meta"><strong>${escapeHtml(res.author.display_name)}</strong> · ${new Date().toLocaleString()}</div>
            <div class="ugc-comment__text">${formatHashtags(res.content)}</div>
          </div>
        </div>
      `);

      // Update comment counter in the feed card
      const postCard = feedEl.querySelector(`[data-post-id="${state.activePostForComments}"]`);
      if(postCard){
        const commentBtn = postCard.querySelector('[data-action="comments"] span');
        if(commentBtn){
          const currentCount = parseInt(commentBtn.textContent, 10) || 0;
          commentBtn.textContent = String(currentCount + 1);
        }
      }
    }catch(e){
      setError(commentsErrorEl, e.message || 'Erreur commentaire.');
    }
  }

  async function openProfileModal(){
    if(!profileModal) return;
    if(!state.myProfile){
      setError(profileErrorEl, 'Vous devez d\'abord créer un profil.');
      return;
    }

    setError(profileErrorEl, '');

    // Pre-fill current profile info
    const previewAvatar = qs('[data-profile-avatar-preview]', profileModal);
    const previewName = qs('[data-profile-name-preview]', profileModal);

    if(previewAvatar) {
      previewAvatar.innerHTML = `<img class="ugc-avatar ugc-avatar--lg" src="${state.myProfile.avatar_url}" alt="" />`;
    }
    if(previewName) {
      previewName.textContent = state.myProfile.display_name;
    }

    // Clear input fields
    qs('[data-input="profile_display_name"]', profileModal).value = '';
    qs('[data-input="profile_pin"]', profileModal).value = '';
    qs('[data-input="profile_avatar_file"]', profileModal).value = '';

    openModal(profileModal);
  }

  function openAvatarPicker(){
    const fileInput = qs('[data-input="profile_avatar_file"]', profileModal);
    if(fileInput) fileInput.click();
  }

  async function submitProfile(){
    setError(profileErrorEl, '');

    if(!state.myProfile){
      setError(profileErrorEl, 'Profil non trouvé.');
      return;
    }

    const newDisplayName = (qs('[data-input="profile_display_name"]', profileModal).value || '').trim();
    const pin = (qs('[data-input="profile_pin"]', profileModal).value || '').trim();
    const avatarFile = qs('[data-input="profile_avatar_file"]', profileModal).files[0];

    if(!pin){
      setError(profileErrorEl, 'PIN requis pour modifier le profil.');
      return;
    }

    if(!/^[0-9]{4,6}$/.test(pin)){
      setError(profileErrorEl, 'PIN invalide (4–6 chiffres).');
      return;
    }

    try{
      let avatar_attachment_id = null;

      // Upload new avatar if provided
      if(avatarFile){
        const up = await uploadMedia(avatarFile);
        avatar_attachment_id = up.id;
      }

      // Prepare update data
      const updateData = {
        display_name: newDisplayName || state.myProfile.display_name,
        pin: pin
      };

      if(avatar_attachment_id){
        updateData.avatar_attachment_id = avatar_attachment_id;
      }

      const updatedProfile = await api('/profile/upsert', {
        method: 'POST',
        body: JSON.stringify(updateData)
      });

      state.myProfile = updatedProfile;
      setComposerIdentityUI();

      // Update header avatar
      renderAvatarHost(updatedProfile.avatar_url);

      closeModal(profileModal);

      // Clear fields
      qs('[data-input="profile_display_name"]', profileModal).value = '';
      qs('[data-input="profile_pin"]', profileModal).value = '';
      qs('[data-input="profile_avatar_file"]', profileModal).value = '';

    }catch(e){
      setError(profileErrorEl, e.message || 'Erreur lors de la mise à jour du profil.');
    }
  }

  async function deletePost(postId, card){
    if(!confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) return;

    try{
      await api(`/posts/${postId}`, { method: 'DELETE' });

      // Remove from DOM with animation
      card.style.opacity = '0';
      card.style.transform = 'scale(0.95)';
      card.style.transition = 'all 0.3s ease';

      setTimeout(() => {
        card.remove();
      }, 300);
    }catch(e){
      alert(e.message || 'Erreur lors de la suppression.');
    }
  }

  async function editPost(postId, card){
    if(!composerModal) return;

    try{
      // Fetch post data
      const postData = await api(`/posts/${postId}`, { method: 'GET' });

      setError(errorEl, '');

      // Store editing state
      state.editingPostId = postId;

      // Pre-fill caption
      const captionInput = qs('[data-input="caption"]', composerModal);
      if(captionInput) captionInput.value = postData.caption || '';

      // Clear media file input (can't pre-fill file inputs)
      const mediaInput = qs('[data-input="media_file"]', composerModal);
      if(mediaInput) mediaInput.value = '';

      // Show composer with pre-filled data
      openModal(composerModal);
      showAuthBlock(false);
      window.showComposerStep('post');

      // Change button text
      const submitBtn = qs('[data-action="submit-post"]', composerModal);
      if(submitBtn) submitBtn.textContent = 'Mettre à jour';

      if(captionInput) setTimeout(() => captionInput.focus(), 50);
    }catch(e){
      alert(e.message || 'Erreur lors du chargement du post.');
    }
  }

  // Tabs
  qsa('.ugc-tab', el).forEach(btn => {
    btn.addEventListener('click', async () => {
      qsa('.ugc-tab', el).forEach(b=>b.classList.remove('is-active'));
      btn.classList.add('is-active');
      state.mode = btn.dataset.mode || 'latest';
      await loadFeed(true);
    });
  });

  // Global click delegation
  el.addEventListener('click', async (ev) => {
    const t = ev.target.closest('[data-action]');
    if(!t) return;

    const action = t.dataset.action;
    if(action === 'open-composer'){
      setError(errorEl,'');
      if(!composerModal) return;
      openModal(composerModal);

      // If already connected on this phone, go straight to the publish form
      if(state.myProfile){
        showAuthBlock(false);
        window.showComposerStep('post');
        const ta = qs('[data-input="caption"]', composerModal);
        if(ta) setTimeout(()=>ta.focus(), 50);
      }else{
        // Otherwise: identity step first
        window.showComposerStep('auth');
        showAuthBlock(true);
      }
    }
    if(action === 'pick-media'){
      ev.stopPropagation();
      setError(errorEl,'');
      if(!composerModal) return;
      openModal(composerModal);
      showAuthBlock(!state.myProfile);
      resetComposerStep();
      const kind = t.dataset.pick || 'photo';
      configureMediaPicker(kind);
      // If already authenticated, open picker immediately
      if(state.myProfile){
        const mediaInput = qs('[data-input="media_file"]', composerModal);
        if(mediaInput) mediaInput.click();
      }
    }
    if(action === 'close-modal'){
      closeModal(composerModal);
      closeModal(commentsModal);
      closeModal(profileModal);
    }
    if(action === 'auth-continue'){
      setError(errorEl,'');
      try{
        if(!state.myProfile){
          await ensureProfileFromComposer();
        }
        setComposerIdentityUI();
        showAuthBlock(false);
        window.showComposerStep('post');
        const ta = qs('[data-input="caption"]', composerModal);
        if(ta) setTimeout(()=>ta.focus(), 50);
      }catch(e){
        setError(errorEl, e.message || 'Erreur.');
      }
    }
    if(action === 'submit-post'){
      await submitPost();
    }
    if(action === 'load-more'){
      state.page += 1;
      await loadFeed(false);
    }
    if(action === 'comments'){
      const card = ev.target.closest('[data-post-id]');
      if(!card) return;
      await openComments(parseInt(card.dataset.postId,10));
    }
    if(action === 'submit-comment'){
      await submitComment();
    }
    if(action === 'switch-user'){
      // Forget local profile for this device only
      state.myProfile = null;
      setComposerIdentityUI();
      showAuthBlock(true);
      window.showComposerStep('auth');
    }
    if(action === 'like'){
      const card = ev.target.closest('[data-post-id]');
      if(!card) return;
      await toggleLike(parseInt(card.dataset.postId,10), t);
    }
    if(action === 'open-profile'){
      await openProfileModal();
    }
    if(action === 'submit-profile'){
      await submitProfile();
    }
    if(action === 'open-avatar-picker'){
      openAvatarPicker();
    }
    if(action === 'delete-post'){
      const card = ev.target.closest('[data-post-id]');
      if(!card) return;
      await deletePost(parseInt(card.dataset.postId,10), card);
    }
    if(action === 'edit-post'){
      const card = ev.target.closest('[data-post-id]');
      if(!card) return;
      await editPost(parseInt(card.dataset.postId,10), card);
    }
  });

  // Boot: try to load profile for this device
  (async function(){
    try{
      const me = await api('/profile/me', { method:'GET' });
      state.myProfile = me;
    }catch(e){
      state.myProfile = null;
    }finally{
      setComposerIdentityUI();
      resetComposerStep();
      loadFeed(true);
    }
  })();
})();