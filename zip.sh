#!/bin/bash
echo "Zipping extension";
tar -czf tvpage_magento.tar.gz app/;
zip -r tvpage_magento.zip app/
