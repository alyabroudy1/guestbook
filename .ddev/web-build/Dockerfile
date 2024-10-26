
#ddev-generated
# You can copy this Dockerfile.example to Dockerfile to add configuration
# or packages or anything else to your webimage
# These additions will be appended last to ddev's own Dockerfile
#RUN npm install --global forever
#RUN echo "Built on $(date)" > /build-date.txt
# Install Google Chrome Stable
ARG BASE_IMAGE
FROM $BASE_IMAGE
RUN echo 'installing chrome'
# Install Google Chrome Stable
# .ddev/Dockerfile.chrome

# Install dependencies for Chrome
RUN apt-get update && apt-get install -y \
    wget gnupg2 ca-certificates

# Add Google Chrome’s official key and repository
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - && \
    echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list && \
    apt-get update && \
    apt-get install -y google-chrome-stable && \
    rm -rf /var/lib/apt/lists/*

# Verify Chrome installation
RUN google-chrome --version
