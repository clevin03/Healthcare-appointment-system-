async function fetchModels(providerKey) {
    const btn = event.target.closest('button');
    const section = btn.closest('.provider-section');
    const urlInput = section.querySelector('input[name="' + providerKey + '_api_url"]');
    const keyInput = section.querySelector('input[name="' + providerKey + '_api_key"]');
    const select = document.getElementById(providerKey + '_model_select');

    const apiUrl = urlInput.value.trim();
    const apiKey = keyInput.value.trim();

    if (!apiUrl || !apiKey) {
        alert('Please enter both API URL and API Key first.');
        return;
    }

    btn.classList.add('fetch-loading');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
    select.style.display = 'none';
    select.innerHTML = '<option value="">-- Select a model --</option>';

    try {
        const response = await fetch('api/fetch_models.php?api_url=' + encodeURIComponent(apiUrl) + '&api_key=' + encodeURIComponent(apiKey));

        if (!response.ok) {
            const errData = await response.json().catch(function() { return {}; });
            throw new Error(errData.error || 'HTTP ' + response.status);
        }

        const data = await response.json();
        const models = data.data || [];

        if (models.length === 0) {
            select.innerHTML = '<option value="">-- No models found --</option>';
            select.style.display = 'block';
            return;
        }

        models.forEach(function(m) {
            const id = m.id || m;
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = id;
            select.appendChild(opt);
        });

        select.style.display = 'block';
        select.size = Math.min(models.length + 1, 10);
    } catch (err) {
        alert('Failed to fetch models: ' + err.message);
    } finally {
        btn.classList.remove('fetch-loading');
        btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Fetch Models';
    }
}