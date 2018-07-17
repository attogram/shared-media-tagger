-- Shared Media Tagger Demo Seed v1.1.4

-- Demo Site
INSERT OR REPLACE INTO site (id, name, about) VALUES (
1,
'Shared Media Tagger',
'<p>Welcome to your new website, powered by the <b>Shared Media Tagger</b>.</p>' ||
'<p>How to complete your installation:' ||
  '<ol>' ||
    '<li>Create config file in <code>public/</code> directory: ' ||
      '<code>cp config.example.php config.php</code>' ||
    '<li>Edit <code>public/config.php</code>: Uncomment <code>$config[''adminConfigFile'']</code> ' ||
      'and point it to a <b>secure</b> location</li>' ||
    '<li>Create admin config file in your secure location: ' ||
        '<code>cp config.admin.example.php /secure/location/config.admin.php</code></li>' ||
    '<li>Edit <code>config.admin.php</code>: create your admin username/password logins.  ' ||
      'Default logins are: admin1/admin1, admin2/admin2</li>' ||
    '<li><a href="login/">Login to the Curators Backend</a></li>' ||
  '</ol>' ||
'</p>' ||
'<p>Setup your site:' ||
  '<ul>' ||
    '<li><a href="admin/site">SITE admin</a>: site name, this about message, headers, footers, curation mode on/off</li>' ||
    '<li><a href="admin/category">CATEGORY admin</a>: find categories, import category info, import media from category, and more</li>' ||
    '<li><a href="admin/tag">TAGS admin</a>: voting tag bar settings</li>' ||
    '<li><a href="admin/media">MEDIA admin</a>: add media, and more</li>' ||
    '<li><a href="admin/curate">CURATE admin</a>: Lightboard curation tool</li>' ||
    '<li><a href="admin/user">USER admin</a>: view and delete users</li>' ||
    '<li><a href="admin/database">DATABASE admin</a>: db tools</li>' ||
  '</ul>' ||
'</p>'
);

-- Tag Set
INSERT OR REPLACE INTO tag (id, position, score, name, display_name)
VALUES (1, 1, 5, '😊 Best', '😊');

INSERT OR REPLACE INTO tag (id, position, score, name, display_name)
VALUES (2, 2, 4, '🙂 Good', '🙂');

INSERT OR REPLACE INTO tag (id, position, score, name, display_name)
VALUES (3, 3, 3, '😐 OK', '😐');

INSERT OR REPLACE INTO tag (id, position, score, name, display_name)
VALUES (4, 4, 2, '🙁 Unsure', '🙁');

INSERT OR REPLACE INTO tag (id, position, score, name, display_name)
VALUES (5, 5, 1, '☹️ Bad', '☹️');
