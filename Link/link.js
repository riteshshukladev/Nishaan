function navigateToLink(foldername, user_id) {
  const tabId = new URLSearchParams(window.location.search).get("tab_id");
  let url = `../Link/linkDisplay.php?user_id=${encodeURIComponent(
    user_id
  )}&foldername=${encodeURIComponent(foldername)}`;
  if (tabId) {
    url += `&tab_id=${encodeURIComponent(tabId)}`;
  }
  window.location.href = url;
}
