/* Presenter-controlled cinematic reveal, loaded only by admin/result-reveal.php. */
(() => {
  'use strict';

  const REVEAL_ORDER = ['Female Treasurer', 'Male Treasurer', 'Secretary', 'Vice President', 'President'];
  const DEFAULT_PHOTO = '../assets/candidates/candidate-a.svg';
  const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[character]));
  const photoFor = candidate => /^\.\.\/uploads\/candidates\/[a-f0-9]{32}\.(?:jpg|png|webp)$/.test(candidate?.photo || candidate?.photo_path || '')
    ? (candidate.photo || candidate.photo_path)
    : DEFAULT_PHOTO;

  let host;
  let positions = [];
  let revealStep = -1;
  let revealStage = 'intro';
  let revealCandidateIndex = 0;

  function particles(count = 34) {
    return Array.from({length: count}, (_, index) => {
      const left = (index * 37 + 7) % 100;
      const delay = ((index * 11) % 24) / 10;
      const duration = 3.6 + ((index * 7) % 18) / 10;
      const size = 2 + ((index * 5) % 5);
      const drift = -44 + ((index * 17) % 88);
      return `<span class="cinema-particle" style="--left:${left}%;--delay:${delay}s;--duration:${duration}s;--size:${size}px;--drift:${drift}px"></span>`;
    }).join('');
  }

  function progressDots() {
    return positions.map((_, index) => `<span class="cinema-progress-dot ${index < revealStep ? 'done' : ''} ${index === revealStep ? 'active' : ''}"></span>`).join('');
  }

  function progress() {
    return `<div class="cinema-progress-wrap" aria-label="Result ${revealStep + 1} of ${positions.length}">
      <span class="cinema-progress-label">Result ${String(revealStep + 1).padStart(2, '0')}</span>
      <div class="cinema-progress-dots">${progressDots()}</div>
      <span class="cinema-progress-label">${String(positions.length).padStart(2, '0')}</span>
    </div>`;
  }

  function ambience(count = 18, className = 'cinema-particles-soft') {
    return `<div class="cinema-light cinema-light-left"></div><div class="cinema-light cinema-light-right"></div><div class="cinema-vignette"></div>
      <div class="cinema-particles ${className}" aria-hidden="true">${particles(count)}</div>`;
  }

  function announceStage() {
    const heading = host.querySelector('h1,h2');
    if (!heading) return;
    heading.setAttribute('tabindex', '-1');
    heading.focus({preventScroll: true});
  }

  function renderIntro() {
    revealStep = -1;
    revealStage = 'intro';
    revealCandidateIndex = 0;
    host.innerHTML = `<section class="cinema-stage cinema-opening" aria-labelledby="cinema-title">
      ${ambience(28, '')}
      <div class="cinema-opening-content text-center">
        <div class="cinema-logo-wrap"><span class="cinema-logo-ring"></span><img src="../assets/amsa-logo.png" alt="AMSA AIU" class="cinema-logo"></div>
        <p class="cinema-kicker mt-4">Official Results Presentation</p>
        <h1 id="cinema-title" class="cinema-main-title">AMSA AIU<br><span>7th Election 2026</span></h1>
        <div class="cinema-gold-rule" aria-hidden="true"><span></span></div>
        <p class="cinema-tagline">We Rise by Lifting Others</p>
        <p class="cinema-intro-copy">Five elected positions. Every candidate introduced. Every stage controlled by the presenter.</p>
        <button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa cinema-begin-btn" id="beginReveal"><span>Begin Cinematic Reveal</span><i class="fa-solid fa-play"></i></button>
        <p class="cinema-hint mt-3 mb-0">Presenter-controlled mode &middot; nothing advances automatically.</p>
      </div>
    </section>`;
    host.querySelector('#beginReveal')?.addEventListener('click', () => {
      revealStep = 0;
      revealStage = 'position';
      renderCurrentStage();
    });
  }

  function renderPosition(position) {
    host.innerHTML = `<section class="cinema-stage cinema-position-intro">
      <div class="cinema-curtain cinema-curtain-left" aria-hidden="true"></div><div class="cinema-curtain cinema-curtain-right" aria-hidden="true"></div>
      ${ambience()}${progress()}
      <div class="cinema-position-content text-center">
        <p class="cinema-kicker cinema-animate-up">Next Position</p><div class="cinema-title-line cinema-animate-line" aria-hidden="true"></div>
        <h1 class="cinema-position-title cinema-title-enter">${escapeHtml(position.name)}</h1>
        <p class="cinema-position-subtitle cinema-animate-up cinema-delay-2">Introduce the candidates when the presenter is ready.</p>
        <div class="cinema-controls mt-5">
          ${revealStep > 0 ? '<button type="button" class="btn-amsa btn-amsa-outline cinema-control-dark" id="previousResultFromPosition"><i class="fa-solid fa-arrow-left"></i> Previous Result</button>' : '<span></span>'}
          <button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa" id="showFirstCandidate">Show First Candidate <i class="fa-solid fa-arrow-right"></i></button>
        </div>
      </div>
    </section>`;
    host.querySelector('#showFirstCandidate')?.addEventListener('click', () => { revealCandidateIndex = 0; revealStage = 'candidate'; renderCurrentStage(); });
    host.querySelector('#previousResultFromPosition')?.addEventListener('click', () => { revealStep--; revealStage = 'result'; renderCurrentStage(); });
  }

  function renderCandidate(position, candidateIndex) {
    const list = position.candidates;
    const candidate = list[candidateIndex];
    host.innerHTML = `<section class="cinema-stage cinema-candidate-showcase">
      <div class="cinema-spotlight" aria-hidden="true"></div>${ambience()}${progress()}
      <div class="cinema-candidate-showcase-content text-center">
        <p class="cinema-kicker">${escapeHtml(position.name)} &middot; Candidate ${candidateIndex + 1} of ${list.length}</p>
        <div class="cinema-showcase-photo-wrap"><span class="cinema-showcase-halo" aria-hidden="true"></span><span class="cinema-showcase-ring" aria-hidden="true"></span>
          <img src="${escapeHtml(photoFor(candidate))}" alt="${escapeHtml(candidate.name)} portrait" class="cinema-showcase-photo">
        </div>
        <p class="cinema-showcase-label">Official Candidate</p><h2 class="cinema-showcase-name">${escapeHtml(candidate.name)}</h2>
        <div class="cinema-showcase-sequence" aria-label="Candidate presentation progress">${list.map((_, index) => `<span class="${index < candidateIndex ? 'done' : ''} ${index === candidateIndex ? 'active' : ''}"></span>`).join('')}</div>
        <p class="cinema-showcase-caption">Candidate ${candidateIndex + 1} of ${list.length} &middot; advance only when the presenter is ready.</p>
        <div class="cinema-candidate-controls mt-4">
          <div class="cinema-candidate-controls-top"><button type="button" class="btn-amsa btn-amsa-outline cinema-control-dark" id="previousCandidateStage"><i class="fa-solid fa-arrow-left"></i> ${candidateIndex === 0 ? 'Back to Position' : 'Previous Candidate'}</button>
            <span class="cinema-control-label">Candidate ${String(candidateIndex + 1).padStart(2, '0')} / ${String(list.length).padStart(2, '0')}</span></div>
          <div class="cinema-candidate-next-wrap"><button type="button" class="btn-amsa btn-amsa-gold cinema-candidate-next" id="nextCandidateStage">${candidateIndex < list.length - 1 ? 'Next Candidate <i class="fa-solid fa-arrow-right"></i>' : 'Show All Candidates <i class="fa-solid fa-users"></i>'}</button></div>
        </div>
      </div>
    </section>`;
    host.querySelector('#previousCandidateStage')?.addEventListener('click', () => {
      if (revealCandidateIndex > 0) revealCandidateIndex--;
      else revealStage = 'position';
      renderCurrentStage();
    });
    host.querySelector('#nextCandidateStage')?.addEventListener('click', () => {
      if (revealCandidateIndex < list.length - 1) revealCandidateIndex++;
      else revealStage = 'hold';
      renderCurrentStage();
    });
  }

  function renderHold(position) {
    const list = position.candidates;
    host.innerHTML = `<section class="cinema-stage cinema-presenter-hold">
      <div class="cinema-spotlight" aria-hidden="true"></div>${ambience(16)}${progress()}
      <div class="cinema-presenter-hold-content text-center">
        <p class="cinema-kicker">${escapeHtml(position.name)}</p><h2 class="cinema-hold-title">The candidates are</h2>
        <p class="cinema-hold-subtitle">The official result remains hidden until the presenter reveals it.</p>
        <div class="cinema-hold-candidates" aria-label="${list.length} candidates for ${escapeHtml(position.name)}">${list.map((candidate, index) => `<article class="cinema-hold-candidate" style="--candidate-delay:${index * 90}ms">
          <div class="cinema-hold-photo-wrap"><img src="${escapeHtml(photoFor(candidate))}" alt="${escapeHtml(candidate.name)} portrait" class="cinema-hold-photo"></div>
          <span class="cinema-hold-number">Candidate ${String(index + 1).padStart(2, '0')}</span><h3>${escapeHtml(candidate.name)}</h3></article>`).join('')}</div>
        <div class="cinema-presenter-status" aria-live="polite"><span class="cinema-live-dot" aria-hidden="true"></span>Presenter hold &middot; waiting for reveal command</div>
        <div class="cinema-hold-actions">
          <button type="button" class="btn-amsa btn-amsa-outline cinema-replay-candidates" id="backToLastCandidate"><i class="fa-solid fa-arrow-left"></i> Back to Last Candidate</button>
          <button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa cinema-reveal-now" id="revealNow">Reveal Official Result <i class="fa-solid fa-wand-magic-sparkles"></i></button>
        </div>
        <p class="cinema-admin-note mb-0">Nothing advances automatically. Click only when the presenter gives the cue.</p>
      </div>
    </section>`;
    host.querySelector('#backToLastCandidate')?.addEventListener('click', () => { revealCandidateIndex = Math.max(0, list.length - 1); revealStage = 'candidate'; renderCurrentStage(); });
    host.querySelector('#revealNow')?.addEventListener('click', () => { revealStage = 'result'; renderCurrentStage(); });
  }

  function resultBar(candidate, total, top, tie) {
    const percentage = total ? Number(candidate.votes) / total * 100 : 0;
    const highlighted = Number(candidate.votes) === top;
    return `<div class="result-card ${highlighted ? 'winner' : ''}"><div class="d-flex align-items-center justify-content-between gap-3 mb-2"><div class="min-w-0">
      <p class="small fw-bold text-truncate mb-0">${escapeHtml(candidate.name)}</p><p class="small text-secondary mb-0">${Number(candidate.votes)} votes</p></div>
      <span class="small fw-bold text-maroon">${percentage.toFixed(1)}%</span></div><div class="result-track"><div class="result-fill" style="width:${percentage}%"></div></div>
      ${tie && highlighted ? '<span class="visually-hidden">Tied highest vote total</span>' : ''}</div>`;
  }

  function resultControls() {
    return `<div class="cinema-result-controls"><button type="button" class="btn-amsa btn-amsa-outline cinema-control-dark" id="backToPresenterHold"><i class="fa-solid fa-arrow-left"></i> Previous Stage</button>
      ${revealStep < positions.length - 1
        ? '<button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa" id="nextReveal">Next Position <i class="fa-solid fa-arrow-right"></i></button>'
        : '<button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa" id="finishReveal">Finish Presentation <i class="fa-solid fa-flag-checkered"></i></button>'}
    </div>`;
  }

  function renderResult(position) {
    const list = position.candidates;
    const total = list.reduce((sum, candidate) => sum + Number(candidate.votes), 0);
    const top = Math.max(...list.map(candidate => Number(candidate.votes)));
    const leaders = list.filter(candidate => Number(candidate.votes) === top);
    const tie = leaders.length > 1;
    const winner = leaders[0];
    const percentage = total ? (top / total * 100).toFixed(1) : '0.0';
    const identity = tie
      ? `<div class="cinema-tie-portraits">${leaders.map(candidate => `<img src="${escapeHtml(photoFor(candidate))}" alt="${escapeHtml(candidate.name)} portrait">`).join('')}</div><p class="cinema-elected-label">Official Tie</p><h1 class="cinema-winner-name">Runoff Required</h1><div class="cinema-winner-stat"><strong>${percentage}% each</strong><span>${top} votes each</span></div>`
      : `<div class="cinema-winner-photo-wrap"><span class="cinema-winner-halo"></span><span class="cinema-winner-ring"></span><img src="${escapeHtml(photoFor(winner))}" alt="${escapeHtml(winner.name)} portrait" class="cinema-winner-photo"><span class="cinema-crown-badge"><i class="fa-solid fa-crown"></i></span></div><p class="cinema-elected-label">Congratulations</p><h1 class="cinema-winner-name">${escapeHtml(winner.name)}</h1><div class="cinema-winner-stat"><strong>${percentage}%</strong><span>${top} votes</span></div>`;
    host.innerHTML = `<section class="cinema-stage cinema-final ${tie ? 'cinema-tie-result' : ''}">
      <div class="cinema-flash" aria-hidden="true"></div><div class="cinema-gold-rays" aria-hidden="true"></div>${ambience(42, 'cinema-particles-burst')}${progress()}
      <div class="cinema-final-inner"><div class="cinema-winner-hero text-center"><p class="cinema-kicker cinema-winner-kicker">${tie ? 'Tied Result' : 'Elected'} &middot; ${escapeHtml(position.name)}</p>${identity}</div>
        <div class="cinema-result-column"><div class="cinema-result-summary"><div class="cinema-summary-head"><div><p class="cinema-kicker mb-1">Official Count</p><h2>${escapeHtml(position.name)} Results</h2></div><div class="cinema-total-votes"><span>Total Votes</span><strong>${total}</strong></div></div>
          <div class="d-grid gap-3 mt-4">${list.map(candidate => resultBar(candidate, total, top, tie)).join('')}</div>
          <p class="cinema-integrity-note mb-0"><i class="fa-solid fa-shield-halved"></i>${tie ? 'A tie exists at the highest verified vote total. No single winner is declared; election administrators must follow the runoff process.' : `Highest verified vote total for ${escapeHtml(position.name)}.`}</p>
        </div>${resultControls()}</div></div>
    </section>`;
    host.querySelector('#backToPresenterHold')?.addEventListener('click', () => { revealStage = 'hold'; renderCurrentStage(); });
    host.querySelector('#nextReveal')?.addEventListener('click', () => { revealStep++; revealCandidateIndex = 0; revealStage = 'position'; renderCurrentStage(); });
    host.querySelector('#finishReveal')?.addEventListener('click', () => { revealStage = 'finished'; renderCurrentStage(); });
  }

  function renderFinished() {
    host.innerHTML = `<section class="cinema-stage cinema-opening">${ambience(34, '')}<div class="cinema-opening-content text-center">
      <div class="cinema-logo-wrap"><span class="cinema-logo-ring"></span><img src="../assets/amsa-logo.png" alt="AMSA AIU" class="cinema-logo"></div>
      <p class="cinema-kicker mt-4">Official Results Presentation</p><h1 class="cinema-main-title">Results Reveal<br><span>Complete</span></h1>
      <div class="cinema-gold-rule" aria-hidden="true"><span></span></div><p class="cinema-tagline">AMSA AIU Election 2026</p>
      <div class="d-flex flex-wrap justify-content-center gap-3 mt-4"><button type="button" class="btn-amsa btn-amsa-outline cinema-control-dark" id="backToFinalResult"><i class="fa-solid fa-arrow-left"></i> Back to Final Result</button>
        <button type="button" class="btn-amsa btn-amsa-gold btn-lg-amsa" id="restartReveal"><i class="fa-solid fa-rotate-left"></i> Restart Presentation</button></div>
    </div></section>`;
    host.querySelector('#backToFinalResult')?.addEventListener('click', () => { revealStep = positions.length - 1; revealStage = 'result'; renderCurrentStage(); });
    host.querySelector('#restartReveal')?.addEventListener('click', renderIntro);
  }

  function renderCurrentStage() {
    if (revealStage === 'intro') return renderIntro();
    if (revealStage === 'finished') { renderFinished(); announceStage(); return; }
    const position = positions[revealStep];
    if (!position) return renderIntro();
    if (!position.candidates.length) {
      host.innerHTML = `<section class="reveal-empty-state" role="status"><div class="reveal-empty-icon"><i class="fa-solid fa-users-slash" aria-hidden="true"></i></div><p class="reveal-kicker">${escapeHtml(position.name)}</p><h1>No Candidates Available</h1><p>The official result presentation cannot continue because this position has no candidates.</p></section>`;
      return;
    }
    if (revealStage === 'position') renderPosition(position);
    else if (revealStage === 'candidate') {
      revealCandidateIndex = Math.max(0, Math.min(revealCandidateIndex, position.candidates.length - 1));
      renderCandidate(position, revealCandidateIndex);
    } else if (revealStage === 'hold') renderHold(position);
    else if (revealStage === 'result') renderResult(position);
    else renderIntro();
    announceStage();
  }

  function mount(target, resultData) {
    host = target;
    const source = Array.isArray(resultData?.positions) ? resultData.positions : [];
    positions = REVEAL_ORDER.map(name => source.find(position => position.name === name)).filter(Boolean).map(position => ({
      id: Number(position.id),
      name: String(position.name),
      candidates: Array.isArray(position.candidates) ? position.candidates.map(candidate => ({
        id: Number(candidate.id), name: String(candidate.name), photo: candidate.photo || candidate.photo_path || '', votes: Number(candidate.votes) || 0
      })) : []
    }));
    revealStep = -1;
    revealStage = 'intro';
    revealCandidateIndex = 0;
    if (!positions.length) {
      host.innerHTML = '<section class="reveal-empty-state" role="status"><div class="reveal-empty-icon"><i class="fa-solid fa-chart-column" aria-hidden="true"></i></div><p class="reveal-kicker">Official Results Presentation</p><h1>No Results Available</h1><p>No election positions are available for this presentation.</p></section>';
      return;
    }
    renderIntro();
  }

  window.AmsaResultReveal = {mount};
})();
