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
  const errorEl = qs('[data-error]', el);
  const commentsErrorEl = qs('[data-comments-error]', el);
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

    return `
      <div class="ugc-card" data-post-id="${item.id}">
        <div class="ugc-card__head">
          <img class="ugc-avatar" src="${item.author.avatar_url}" alt="" />
          <div class="ugc-author">
            <div class="ugc-author__name">${escapeHtml(item.author.display_name)}</div>
            <div class="ugc-author__date">${new Date(item.date).toLocaleString()}</div>
          </div>
        </div>
        ${mediaHtml}
        ${item.caption ? `<div class="ugc-caption">${item.caption}</div>` : ``}
        <div class="ugc-actions">
          <button class="ugc-btn ugc-btn-like ${item.liked_by_me ? 'is-liked':''}" data-action="like">♥ <span data-like-count>${item.like_count}</span></button>
          <button class="ugc-btn" data-action="comments">💬 <span>${item.comment_count}</span></button>
        </div>
      </div>
    `;
  }

  function escapeHtml(str){
    return String(str||'').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
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
      await ensureProfileFromComposer();

      let media_ids = [];
      let media_type = 'none';

      if(mediaFile){
        const up = await uploadMedia(mediaFile);
        media_ids = [up.id];
        media_type = (up.mime_type && up.mime_type.startsWith('video/')) ? 'video' : 'image';
      }

      const post = await api('/posts', {
        method: 'POST',
        body: JSON.stringify({ caption, media_type, media_ids })
      });

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
            <div class="ugc-comment__text">${c.content}</div>
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
        setError(commentsErrorEl, 'Veuillez d’abord définir votre pseudonyme et PIN (une seule fois).');
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
            <div class="ugc-comment__text">${res.content}</div>
          </div>
        </div>
      `);
    }catch(e){
      setError(commentsErrorEl, e.message || 'Erreur commentaire.');
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