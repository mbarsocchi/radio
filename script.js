function toggleSpinner(){
  const overlay = document.getElementById('loaderOverlay');
  if (overlay.classList.contains('active')) {
    overlay.classList.remove('active');
  }else {
    overlay.classList.add('active');
  }
}


const API_ENDPOINT = 'api.php';
const streamItems = document.querySelectorAll('.stream-item');
streamItems.forEach(item => {
  item.addEventListener('click', async function() {
    const streamUrl = this.getAttribute('data-stream-url');
    const action = this.getAttribute('data-action');
    const requestBody = {};
    if (streamUrl !== null && streamUrl !== undefined) {
      requestBody.stream = streamUrl;
    }
    if (action !== null && action !== undefined) {
      requestBody.action = action;
    }
    try {
      toggleSpinner()
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestBody)
      });
      if (response.ok) {
        const data = await response.json();
        location.reload();
      }
    } catch (error) {
      console.error('Errore di rete o di connessione:', error);
    }
  });
});